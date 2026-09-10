<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Source;
use App\Models\Service;
use App\Models\User;
use App\Models\EnquiryFollowUp;
use App\Models\EnquiryComment;
use App\Exports\EnquirySampleExport;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExcelLeadImportController extends Controller
{
    use APIResponse;

    public function showImportForm()
    {
        $heading = 'Import Excel Leads';
        $sources = Source::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('master.enquiry.excel_import', compact('heading', 'sources', 'users'));
    }

    public function downloadSampleTemplate()
    {
        return Excel::download(new EnquirySampleExport, 'enquiry_leads_sample_template.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $tempFileName = uniqid('excel_lead_') . '.' . $extension;
        $delimiter = ',';
        $headers = [];
        $previewData = [];

        try {
            if (in_array($extension, ['xls', 'xlsx'])) {
                $readerType = $extension === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::XLS;
                $sheetData = Excel::toArray(new \stdClass, $path, null, $readerType)[0] ?? [];
                
                if (!empty($sheetData)) {
                    // Extract headers from the first row and filter empty headers
                    $rawHeaders = $sheetData[0] ?? [];
                    $headers = array_values(array_filter(array_map('trim', array_map('strval', $rawHeaders))));
                    
                    $rows = array_slice($sheetData, 1, 5);
                    foreach ($rows as $row) {
                        if (empty(array_filter($row, fn($val) => !is_null($val) && $val !== ''))) continue;
                        $paddedRow = array_pad(array_slice($row, 0, count($headers)), count($headers), '');
                        $previewData[] = array_combine($headers, $paddedRow);
                    }
                }
                $tempPath = $file->storeAs('temp', $tempFileName);
            } else {
                // Read CSV/TXT content and normalize character encoding
                $content = file_get_contents($path);
                $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'ASCII'], true);

                if ($encoding && $encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                    file_put_contents($path, $content);
                }

                // Detect delimiter (comma or tab or semicolon)
                $lines = explode("\n", $content);
                $firstLine = $lines[0] ?? '';
                if (strpos($firstLine, "\t") !== false) {
                    $delimiter = "\t";
                } elseif (strpos($firstLine, ';') !== false && substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                    $delimiter = ';';
                } else {
                    $delimiter = ',';
                }

                $csvFile = fopen($path, 'r');
                $rawHeaders = fgetcsv($csvFile, 0, $delimiter);
                $headers = array_values(array_filter(array_map('trim', array_map('strval', $rawHeaders ?: []))));

                $count = 0;
                while (($row = fgetcsv($csvFile, 0, $delimiter)) !== false && $count < 5) {
                    if (empty(array_filter($row, fn($val) => !is_null($val) && $val !== ''))) continue;
                    $paddedRow = array_pad(array_slice($row, 0, count($headers)), count($headers), '');
                    $previewData[] = array_combine($headers, $paddedRow);
                    $count++;
                }
                fclose($csvFile);

                $tempPath = $file->storeAs('temp', $tempFileName);
                Storage::put($tempPath, $content);
            }

            return response()->json([
                'status' => 'success',
                'headers' => $headers,
                'preview' => $previewData,
                'temp_path' => $tempPath,
                'delimiter' => $delimiter
            ]);
        } catch (\Exception $e) {
            Log::error('Lead import preview error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to read file: ' . $e->getMessage()
            ], 422);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'mapping' => 'required|array',
            'source_id' => 'nullable',
            'new_source_name' => 'nullable|string|max:100',
            'default_assigned_to' => 'nullable|exists:users,id',
        ]);

        $tempPath = $request->input('temp_path');
        $mapping = $request->input('mapping');
        $defaultAssignedTo = $request->input('default_assigned_to');

        if (!Storage::exists($tempPath)) {
            return $this->error('File expired or not found. Please upload again.', 422);
        }

        // Determine Source
        $sourceId = null;
        if (!empty($request->new_source_name)) {
            $createdSource = Source::firstOrCreate(['name' => trim($request->new_source_name)]);
            $sourceId = $createdSource->id;
        } elseif (!empty($request->source_id)) {
            $sourceId = $request->source_id;
        } else {
            $defaultSource = Source::firstOrCreate(['name' => 'Excel Import']);
            $sourceId = $defaultSource->id;
        }

        $extension = strtolower(pathinfo($tempPath, PATHINFO_EXTENSION));
        $services = Service::all();

        $importedCount = 0;
        $updatedCount = 0;
        $duplicateCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            if (in_array($extension, ['xls', 'xlsx'])) {
                $data = Excel::toArray(new \stdClass, $tempPath)[0] ?? [];
                if (!empty($data)) {
                    $headers = array_map('trim', array_map('strval', $data[0] ?? []));
                    $rows = array_slice($data, 1);
                    foreach ($rows as $row) {
                        if (empty(array_filter($row, fn($val) => !is_null($val) && $val !== ''))) continue;
                        $rowData = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), ''));
                        $this->processRow($rowData, $mapping, $sourceId, $defaultAssignedTo, $services, $importedCount, $updatedCount, $duplicateCount);
                    }
                }
            } else {
                $content = Storage::get($tempPath);
                $lines = explode("\n", $content);
                $firstLine = $lines[0] ?? '';
                $delimiter = strpos($firstLine, "\t") !== false ? "\t" : (strpos($firstLine, ';') !== false ? ';' : ',');

                $csvFile = fopen('php://temp', 'r+');
                fwrite($csvFile, $content);
                rewind($csvFile);

                $headers = array_map('trim', array_map('strval', fgetcsv($csvFile, 0, $delimiter) ?: []));
                while (($row = fgetcsv($csvFile, 0, $delimiter)) !== false) {
                    if (empty(array_filter($row, fn($val) => !is_null($val) && $val !== ''))) continue;
                    $rowData = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), ''));
                    $this->processRow($rowData, $mapping, $sourceId, $defaultAssignedTo, $services, $importedCount, $updatedCount, $duplicateCount);
                }
                fclose($csvFile);
            }

            DB::commit();
            Storage::delete($tempPath);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel lead import error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->error('An error occurred during import: ' . $e->getMessage(), 500);
        }

        return $this->success([
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'duplicates' => $duplicateCount,
            'errors' => $errorCount
        ], 'Lead import completed successfully');
    }

    private function processRow($data, $mapping, $sourceId, $defaultAssignedTo, $services, &$importedCount, &$updatedCount, &$duplicateCount)
    {
        $rawName = isset($mapping['name']) && !empty($mapping['name']) ? ($data[$mapping['name']] ?? null) : null;
        $name = trim(preg_replace('/\s+/', ' ', (string) $rawName));
        if (empty($name)) {
            // Skip rows without any contact name
            return;
        }

        $rawPhone = isset($mapping['mobile']) && !empty($mapping['mobile']) ? ($data[$mapping['mobile']] ?? null) : null;
        $cleanPhone = $this->cleanPhone($rawPhone);

        $email = isset($mapping['email']) && !empty($mapping['email']) ? trim((string) ($data[$mapping['email']] ?? '')) : null;
        $location = isset($mapping['location']) && !empty($mapping['location']) ? trim((string) ($data[$mapping['location']] ?? '')) : null;
        $address = isset($mapping['address']) && !empty($mapping['address']) ? trim((string) ($data[$mapping['address']] ?? '')) : null;
        $gstin = isset($mapping['gstin']) && !empty($mapping['gstin']) ? trim((string) ($data[$mapping['gstin']] ?? '')) : null;

        // Feedback, remarks & call status
        $callStatus = isset($mapping['call_status']) && !empty($mapping['call_status']) ? trim((string) ($data[$mapping['call_status']] ?? '')) : null;
        $feedback = isset($mapping['feedback']) && !empty($mapping['feedback']) ? trim((string) ($data[$mapping['feedback']] ?? '')) : null;

        // Appointment / Follow-up date
        $appointmentRaw = isset($mapping['appointment']) && !empty($mapping['appointment']) ? ($data[$mapping['appointment']] ?? null) : null;
        $appointmentDate = $this->parseImportDate($appointmentRaw);

        // Service Requirement Mapping
        $serviceId = null;
        $serviceRequirementText = null;
        if (isset($mapping['service_answer']) && !empty($mapping['service_answer'])) {
            $serviceRequirementText = trim((string) ($data[$mapping['service_answer']] ?? ''));
            if ($serviceRequirementText) {
                $answerLower = strtolower($serviceRequirementText);
                $matchedService = $services->first(function ($s) use ($answerLower) {
                    return strpos($answerLower, strtolower($s->name)) !== false || strpos(strtolower($s->name), $answerLower) !== false;
                });
                if ($matchedService) {
                    $serviceId = $matchedService->id;
                }
            }
        }

        // Priority Mapping
        $priority = 'Medium';
        if (isset($mapping['priority_answer']) && !empty($mapping['priority_answer'])) {
            $pVal = strtolower((string) ($data[$mapping['priority_answer']] ?? ''));
            if (strpos($pVal, 'urgent') !== false || strpos($pVal, 'immediate') !== false || strpos($pVal, 'high') !== false) {
                $priority = 'High';
            } elseif (strpos($pVal, 'low') !== false || strpos($pVal, 'not interested') !== false) {
                $priority = 'Low';
            }
        }

        // Description compilation
        $descParts = [];
        if ($callStatus) {
            $descParts[] = "Call Status: " . $callStatus;
        }
        if ($feedback) {
            $descParts[] = "Feedback: " . $feedback;
        }
        if ($serviceRequirementText && !$serviceId) {
            $descParts[] = "Requirement: " . $serviceRequirementText;
        }
        $combinedDescription = implode("\n", $descParts);

        // ----------------------------------------------------
        // Deduplication & Existing Record Check
        // ----------------------------------------------------
        $existing = null;
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 7) {
            $existing = Enquiry::where('mobile', $cleanPhone)->first();
        }

        if ($existing) {
            // Update existing record: backfill missing fields without overwriting existing data
            $updates = [];
            if (empty($existing->email) && !empty($email)) $updates['email'] = $email;
            if (empty($existing->location) && !empty($location)) $updates['location'] = $location;
            if (empty($existing->address) && !empty($address)) $updates['address'] = $address;
            if (empty($existing->gstin) && !empty($gstin)) $updates['gstin'] = $gstin;
            if (empty($existing->service_id) && !empty($serviceId)) $updates['service_id'] = $serviceId;
            if ($appointmentDate && empty($existing->next_follow_up_at)) {
                $updates['next_follow_up_at'] = $appointmentDate;
            }

            if (!empty($updates)) {
                $existing->update($updates);
            }

            // Append new comment if feedback or call status was captured
            if (!empty($combinedDescription)) {
                $existing->comments()->create([
                    'body' => "[Imported Note] " . str_replace("\n", " | ", $combinedDescription),
                    'user_id' => Auth::id()
                ]);
            }

            // Schedule follow-up if appointment provided and not duplicate
            if ($appointmentDate) {
                $followExists = $existing->followUps()->where('scheduled_at', $appointmentDate)->exists();
                if (!$followExists) {
                    $existing->followUps()->create([
                        'scheduled_at' => $appointmentDate,
                        'notes' => $feedback ?: 'Imported appointment follow-up',
                        'created_by' => Auth::id()
                    ]);
                }
            }

            $updatedCount++;
            return;
        }

        // ----------------------------------------------------
        // Create New Lead
        // ----------------------------------------------------
        $status = 'Open';
        if ($callStatus) {
            $statusLower = strtolower($callStatus);
            if (strpos($statusLower, 'won') !== false || strpos($statusLower, 'converted') !== false) {
                $status = 'Won';
            } elseif (strpos($statusLower, 'not exist') !== false || strpos($statusLower, 'invalid') !== false || strpos($statusLower, 'lost') !== false) {
                $status = 'Lost';
            }
        }

        $enquiry = Enquiry::create([
            'name' => $name,
            'mobile' => $cleanPhone,
            'email' => $email,
            'location' => $location,
            'address' => $address,
            'gstin' => $gstin,
            'source_id' => $sourceId,
            'assigned_to' => $defaultAssignedTo ?: null,
            'status' => $status,
            'priority' => $priority,
            'service_id' => $serviceId,
            'description' => $combinedDescription ?: null,
            'next_follow_up_at' => $appointmentDate,
            'reminder_notes' => $appointmentDate ? ($feedback ?: 'Follow-up appointment') : null,
        ]);

        // Add initial comment if feedback exists
        if (!empty($combinedDescription)) {
            $enquiry->comments()->create([
                'body' => "[Imported Note] " . str_replace("\n", " | ", $combinedDescription),
                'user_id' => Auth::id()
            ]);
        }

        // Schedule follow-up
        if ($appointmentDate) {
            $enquiry->followUps()->create([
                'scheduled_at' => $appointmentDate,
                'notes' => $feedback ?: 'Imported appointment follow-up',
                'created_by' => Auth::id()
            ]);
        }

        $importedCount++;
    }

    private function cleanPhone($phone)
    {
        if (!$phone) return null;
        // Strip p:, +91, 91- prefixes if present
        $cleaned = trim((string) $phone);
        $cleaned = preg_replace('/^(p:|\+91|91[\s\-])/i', '', $cleaned);
        // Strip all non-digits
        $digits = preg_replace('/[^0-9]/', '', $cleaned);
        return $digits ?: null;
    }

    private function parseImportDate($value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            if ($value > 100000000) {
                // Unix timestamp
                try { return \Carbon\Carbon::createFromTimestamp($value); } catch (\Exception $e) {}
            } elseif ($value > 20000 && $value < 100000) {
                // Excel date serial number
                try { return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)); } catch (\Exception $e) {}
            }
        }

        // Standard string date parsing (e.g. "2026-03-25", "25/03/2026", "25-03-2026 14:30")
        try {
            $str = trim((string) $value);
            // If date is in DD/MM/YYYY format
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}/', $str)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', substr($str, 0, 10));
            }
            return \Carbon\Carbon::parse($str);
        } catch (\Exception $e) {
            return null;
        }
    }
}
