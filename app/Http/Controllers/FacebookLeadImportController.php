<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Source;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacebookLeadImportController extends Controller
{
    use APIResponse;

    public function showImportForm()
    {
        $heading = 'Import Facebook Leads';
        return view('master.enquiry.import', compact('heading'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        
        $tempFileName = uniqid() . '.' . $extension;
        $delimiter = ',';
        $headers = [];
        $previewData = [];

        if (in_array($extension, ['xls', 'xlsx'])) {
            $readerType = $extension === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::XLS;
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $path, null, $readerType)[0] ?? [];
            if (!empty($data)) {
                $headers = $data[0] ?? [];
                $rows = array_slice($data, 1, 5);
                foreach ($rows as $row) {
                    $previewData[] = array_combine($headers, array_pad($row, count($headers), ''));
                }
            }
            $tempPath = $file->storeAs('temp', $tempFileName);
        } else {
            // Read file content and detect encoding
            $content = file_get_contents($path);
            $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'], true);
            
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                // Save converted content back to temp file for processing
                file_put_contents($path, $content);
            }

            // Detect delimiter
            $lines = explode("\n", $content);
            $firstLine = $lines[0] ?? '';
            $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

            // Re-read file with fgetcsv for proper parsing
            $csvFile = fopen($path, 'r');
            $headers = fgetcsv($csvFile, 0, $delimiter);
            
            $count = 0;
            while (($row = fgetcsv($csvFile, 0, $delimiter)) !== false && $count < 5) {
                if (count($headers) == count($row)) {
                    $previewData[] = array_combine($headers, $row);
                } else {
                    $previewData[] = array_combine($headers, array_pad($row, count($headers), ''));
                }
                $count++;
            }
            fclose($csvFile);

            // Store file temporarily
            $tempPath = $file->storeAs('temp', $tempFileName);
            // Ensure the stored temp file is also UTF-8
            \Illuminate\Support\Facades\Storage::put(storage_path('app/' . $tempPath), $content);
        }

        return response()->json([
            'status' => 'success',
            'headers' => $headers,
            'preview' => $previewData,
            'temp_path' => $tempPath,
            'delimiter' => $delimiter
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'mapping' => 'required|array',
        ]);

        $mapping = $request->input('mapping');
        $tempPath = $request->input('temp_path');

        if (!\Illuminate\Support\Facades\Storage::exists($tempPath)) {
            return $this->error('File expired or not found. Please upload again.', 422);
        }

        $extension = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));
        $fbSource = Source::firstOrCreate(['name' => 'Facebook']);
        $services = Service::all();

        $importedCount = 0;
        $updatedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            if (in_array($extension, ['xls', 'xlsx'])) {
                $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $tempPath)[0] ?? [];
                if (!empty($data)) {
                    $headers = $data[0] ?? [];
                    $rows = array_slice($data, 1);
                    foreach ($rows as $row) {
                        if (empty(array_filter($row))) continue;
                        $rowData = array_combine($headers, array_pad($row, count($headers), ''));
                        $this->processRow($rowData, $mapping, $fbSource, $services, $importedCount, $updatedCount, $duplicateCount);
                    }
                }
            } else {
                $content = \Illuminate\Support\Facades\Storage::get($tempPath);
                $lines = explode("\n", $content);
                $firstLine = $lines[0] ?? '';
                $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

                $csvFile = fopen('php://temp', 'r+');
                fwrite($csvFile, $content);
                rewind($csvFile);

                $headers = fgetcsv($csvFile, 0, $delimiter);
                while (($row = fgetcsv($csvFile, 0, $delimiter)) !== false) {
                    if (empty(array_filter($row))) continue;
                    $rowData = array_combine($headers, array_pad($row, count($headers), ''));
                    $this->processRow($rowData, $mapping, $fbSource, $services, $importedCount, $updatedCount, $duplicateCount);
                }
                fclose($csvFile);
            }
            DB::commit();
            \Illuminate\Support\Facades\Storage::delete($tempPath);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage());
            return $this->error('An error occurred during import: ' . $e->getMessage(), 500);
        }

        return $this->success([
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'duplicates' => $duplicateCount,
            'errors' => $errorCount
        ], 'Import completed successfully');
    }

    private function processRow($data, $mapping, $fbSource, $services, &$importedCount, &$updatedCount, &$duplicateCount)
    {
        $fbLeadId = $data[$mapping['fb_lead_id']] ?? null;
        
        // Check for existing lead
        if ($fbLeadId) {
            $existing = Enquiry::where('fb_lead_id', $fbLeadId)->first();
            if ($existing) {
                // Backfill missing fields on the existing record
                $updates = [];

                if (empty($existing->fb_created_at) && isset($mapping['fb_created_at']) && !empty($data[$mapping['fb_created_at']])) {
                    $updates['fb_created_at'] = \Carbon\Carbon::parse($data[$mapping['fb_created_at']]);
                }
                if (empty($existing->fb_timeline) && isset($mapping['priority_answer']) && !empty($data[$mapping['priority_answer']])) {
                    $updates['fb_timeline'] = $data[$mapping['priority_answer']];
                }
                if (empty($existing->fb_campaign_name) && isset($mapping['fb_campaign_name']) && !empty($data[$mapping['fb_campaign_name']])) {
                    $updates['fb_campaign_name'] = $data[$mapping['fb_campaign_name']];
                }
                if (empty($existing->fb_form_name) && isset($mapping['fb_form_name']) && !empty($data[$mapping['fb_form_name']])) {
                    $updates['fb_form_name'] = $data[$mapping['fb_form_name']];
                }
                if (empty($existing->fb_platform) && isset($mapping['fb_platform']) && !empty($data[$mapping['fb_platform']])) {
                    $updates['fb_platform'] = $data[$mapping['fb_platform']];
                }

                if (!empty($updates)) {
                    $existing->update($updates);
                    $updatedCount++;
                } else {
                    $duplicateCount++;
                }
                return;
            }
        }

        $enquiryData = [
            'source_id' => $fbSource->id,
            'status' => 'Open',
            'fb_lead_id' => $fbLeadId,
            'fb_campaign_name' => $data[$mapping['fb_campaign_name']] ?? null,
            'fb_form_name' => $data[$mapping['fb_form_name']] ?? null,
            'fb_platform' => $data[$mapping['fb_platform']] ?? null,
            'fb_timeline' => $data[$mapping['priority_answer']] ?? null,
            'fb_created_at' => isset($mapping['fb_created_at']) && !empty($data[$mapping['fb_created_at']]) ? \Carbon\Carbon::parse($data[$mapping['fb_created_at']]) : null,
            'name' => $data[$mapping['name']] ?? 'Unknown',
            'email' => $data[$mapping['email']] ?? null,
            'mobile' => $this->cleanPhone($data[$mapping['mobile']] ?? null),
        ];

        // Priority Mapping
        if (isset($mapping['priority_answer'])) {
            $answer = strtolower($data[$mapping['priority_answer']] ?? '');
            if (strpos($answer, '30_days') !== false) {
                $enquiryData['priority'] = 'High';
            } elseif (strpos($answer, 'months') !== false) {
                $enquiryData['priority'] = 'Medium';
            } else {
                $enquiryData['priority'] = 'Low';
            }
        }

        // Service Mapping
        $description = "";
        if (isset($mapping['service_answer'])) {
            $sAnswer = $data[$mapping['service_answer']] ?? '';
            $description .= "Requirement: " . $sAnswer;
            
            $answerLower = strtolower($sAnswer);
            $matchedService = $services->first(function($s) use ($answerLower) {
                return strpos($answerLower, strtolower($s->name)) !== false;
            });
            if ($matchedService) {
                $enquiryData['service_id'] = $matchedService->id;
            }
        }

        if (isset($mapping['priority_answer'])) {
            $pAnswer = $data[$mapping['priority_answer']] ?? '';
            if ($description) $description .= "\n";
            $description .= "Timeline: " . $pAnswer;
        }
        
        $enquiryData['description'] = trim($description);

        Enquiry::create($enquiryData);
        $importedCount++;
    }

    private function cleanPhone($phone)
    {
        if (!$phone) return null;
        // Strip p: and +91 if present
        $phone = str_replace(['p:', '+91'], '', $phone);
        // Remove non-numeric characters
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
