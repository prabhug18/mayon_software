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
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $path = $request->file('file')->getRealPath();
        
        // Detect delimiter
        $file = fopen($path, 'r');
        $firstLine = fgets($file);
        fclose($file);
        $delimiter = strpos($firstLine, "\t") !== false ? "\t" : ",";

        $file = fopen($path, 'r');
        $headers = fgetcsv($file, 0, $delimiter);
        
        $previewData = [];
        $count = 0;
        while (($row = fgetcsv($file, 0, $delimiter)) !== false && $count < 5) {
            $previewData[] = array_combine($headers, array_pad($row, count($headers), ''));
            $count++;
        }
        fclose($file);

        // Store file temporarily
        $tempPath = $request->file('file')->store('temp');

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
            'delimiter' => 'required|string'
        ]);

        $mapping = $request->input('mapping');
        $tempPath = storage_path('app/' . $request->input('temp_path'));
        $delimiter = $request->input('delimiter');

        if (!file_exists($tempPath)) {
            return $this->error('File expired or not found. Please upload again.', 422);
        }

        $file = fopen($tempPath, 'r');
        $headers = fgetcsv($file, 0, $delimiter);
        
        $fbSource = Source::firstOrCreate(['name' => 'Facebook']);
        $services = Service::all();

        $importedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $data = array_combine($headers, array_pad($row, count($headers), ''));
                
                $fbLeadId = $data[$mapping['fb_lead_id']] ?? null;
                
                // Deduplication
                if ($fbLeadId && Enquiry::where('fb_lead_id', $fbLeadId)->exists()) {
                    $duplicateCount++;
                    continue;
                }

                $enquiryData = [
                    'source_id' => $fbSource->id,
                    'status' => 'Open',
                    'fb_lead_id' => $fbLeadId,
                    'fb_campaign_name' => $data[$mapping['fb_campaign_name']] ?? null,
                    'fb_form_name' => $data[$mapping['fb_form_name']] ?? null,
                    'fb_platform' => $data[$mapping['fb_platform']] ?? null,
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
                if (isset($mapping['service_answer'])) {
                    $answer = strtolower($data[$mapping['service_answer']] ?? '');
                    $matchedService = $services->first(function($s) use ($answer) {
                        return strpos($answer, strtolower($s->name)) !== false;
                    });
                    if ($matchedService) {
                        $enquiryData['service_id'] = $matchedService->id;
                    }
                    $enquiryData['description'] = "Requirement: " . ($data[$mapping['service_answer']] ?? '');
                }

                Enquiry::create($enquiryData);
                $importedCount++;
            }
            DB::commit();
            unlink($tempPath);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage());
            return $this->error('An error occurred during import: ' . $e->getMessage(), 500);
        }

        return $this->success([
            'imported' => $importedCount,
            'duplicates' => $duplicateCount,
            'errors' => $errorCount
        ], 'Import completed successfully');
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
