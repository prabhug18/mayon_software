<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Enquiry;
use App\Models\Company;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\TermsCondition;
use App\Models\Vendor;
use App\Models\QuotationItem;
use App\Models\QuotationVendorCost;
use App\Services\QuotationCalculator;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    use APIResponse;

    protected $calculator;

    public function __construct(QuotationCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $heading = 'Quotations';
        if ($request->wantsJson()) {
            $data = Quotation::with(['enquiry', 'company', 'createdBy'])
                ->orderBy('id', 'desc')
                ->get();
            return $this->success($data);
        }
        return view('quotations.index', compact('heading'));
    }

    public function create(Request $request)
    {
        $heading = 'Create Quotation';
        $enquiryId = $request->query('enquiry');
        $enquiry = $enquiryId ? Enquiry::find($enquiryId) : null;
        
        $companies = Company::orderBy('name')->get();
        $enquiries = Enquiry::orderBy('name')->get();
        $services = Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
        $termsConditions = TermsCondition::where('is_active', true)->orderBy('title')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('quotations.create', compact(
            'heading',
            'enquiry',
            'companies',
            'enquiries',
            'services',
            'termsConditions',
            'vendors'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'enquiry_id' => 'nullable|exists:enquiries,id',
            'quotation_no' => 'nullable|string|unique:quotations,quotation_no',
            'quotation_date' => 'required|date',
            'valid_till' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|in:OWN,THIRD_PARTY,MIXED',
            'terms_condition_id' => 'nullable|exists:terms_conditions,id',
            'terms_content' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string',
            'kind_att' => 'nullable|string|max:255',
            'status' => 'nullable|in:DRAFT,SENT,APPROVED,REVISED',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.service_item_id' => 'nullable|exists:service_items,id',
            'items.*.manual_service_name' => 'nullable|string|max:255',
            'items.*.manual_item_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit' => 'required|in:SQM,RMT,SFT,NOS,LS',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.base_cost' => 'nullable|numeric|min:0',
            'items.*.margin_type' => 'required|in:PERCENTAGE,FIXED',
            'items.*.margin_value' => 'required|numeric|min:0',
            'items.*.selling_rate' => 'required|numeric|min:0',
            'items.*.gst_percentage' => 'required|numeric|min:0|max:100',
            'items.*.vendor_id' => 'nullable|exists:vendors,id',
            'items.*.vendor_rate' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->only([
                'company_id',
                'enquiry_id',
                'quotation_date',
                'valid_till',
                'quotation_type',
                'terms_condition_id',
                'terms_content',
                'customer_name',
                'customer_address',
                'kind_att',
                'status'
            ]);
            
            $data['created_by'] = Auth::id();
            $data['status'] = $data['status'] ?? 'DRAFT';

            // Auto-generate quotation number if not provided
            if (empty($request->quotation_no)) {
                $data['quotation_no'] = $this->generateQuotationNumber($data['company_id']);
            } else {
                $data['quotation_no'] = $request->quotation_no;
            }

            // Create quotation
            $quotation = Quotation::create($data);

            // Create items
            foreach ($request->input('items', []) as $itemData) {
                // Calculate item data using calculator
                $calculatedItem = $this->calculator->calculateItemData($itemData);
                
                $item = QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'service_id' => $calculatedItem['service_id'] ?? null,
                    'service_item_id' => $calculatedItem['service_item_id'] ?? null,
                    'manual_service_name' => $calculatedItem['manual_service_name'] ?? null,
                    'manual_item_name' => $calculatedItem['manual_item_name'] ?? null,
                    'description' => $calculatedItem['description'] ?? null,
                    'unit' => $calculatedItem['unit'],
                    'quantity' => $calculatedItem['quantity'],
                    'base_cost' => $calculatedItem['base_cost'] ?? 0,
                    'margin_type' => $calculatedItem['margin_type'],
                    'margin_value' => $calculatedItem['margin_value'],
                    'selling_rate' => $calculatedItem['selling_rate'],
                    'gst_percentage' => $calculatedItem['gst_percentage'],
                    'line_total' => $calculatedItem['line_total']
                ]);

                // Create vendor cost if provided
                if (!empty($itemData['vendor_id']) && !empty($itemData['vendor_rate'])) {
                    QuotationVendorCost::create([
                        'quotation_item_id' => $item->id,
                        'vendor_id' => $itemData['vendor_id'],
                        'vendor_rate' => $itemData['vendor_rate'],
                        'vendor_total' => ($itemData['vendor_rate'] * $itemData['quantity']),
                        'vendor_gst' => 0 // Can be calculated if needed
                    ]);
                }
            }

            // Recalculate quotation totals
            $quotation = $this->calculator->recalculateQuotation($quotation);

            DB::commit();
            return $this->success($quotation->load('items', 'company', 'enquiry'), 'Quotation created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to create quotation: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $heading = 'Quotation Details';
        $quotation = Quotation::with([
            'enquiry',
            'company',
            'termsCondition',
            'items.service',
            'items.serviceItem',
            'items.vendorCost.vendor',
            'createdBy',
            'parentQuotation',
            'revisions'
        ])->findOrFail($id);

        return view('quotations.show', compact('quotation', 'heading'));
    }

    public function edit($id)
    {
        $heading = 'Edit Quotation';
        $quotation = Quotation::with('items.vendorCost')->findOrFail($id);
        
        $companies = Company::orderBy('name')->get();
        $enquiries = Enquiry::orderBy('name')->get();
        $services = Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
        $termsConditions = TermsCondition::where('is_active', true)->orderBy('title')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('quotations.edit', compact(
            'heading',
            'quotation',
            'companies',
            'enquiries',
            'services',
            'termsConditions',
            'vendors'
        ));
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'enquiry_id' => 'nullable|exists:enquiries,id',
            'quotation_no' => 'required|string|unique:quotations,quotation_no,' . $quotation->id,
            'quotation_date' => 'required|date',
            'valid_till' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|in:OWN,THIRD_PARTY,MIXED',
            'terms_condition_id' => 'nullable|exists:terms_conditions,id',
            'terms_content' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string',
            'kind_att' => 'nullable|string|max:255',
            'status' => 'nullable|in:DRAFT,SENT,APPROVED,REVISED',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.service_item_id' => 'nullable|exists:service_items,id',
            'items.*.manual_service_name' => 'nullable|string|max:255',
            'items.*.manual_item_name' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit' => 'required|in:SQM,RMT,SFT,NOS,LS',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.base_cost' => 'nullable|numeric|min:0',
            'items.*.margin_type' => 'required|in:PERCENTAGE,FIXED',
            'items.*.margin_value' => 'required|numeric|min:0',
            'items.*.selling_rate' => 'required|numeric|min:0',
            'items.*.gst_percentage' => 'required|numeric|min:0|max:100',
            'items.*.vendor_id' => 'nullable|exists:vendors,id',
            'items.*.vendor_rate' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->only([
                'company_id',
                'enquiry_id',
                'quotation_no',
                'quotation_date',
                'valid_till',
                'quotation_type',
                'terms_condition_id',
                'terms_content',
                'customer_name',
                'customer_address',
                'kind_att',
                'status'
            ]);

            $quotation->update($data);

            // Delete existing items and vendor costs
            foreach ($quotation->items as $item) {
                $item->vendorCost()->delete();
            }
            $quotation->items()->delete();

            // Recreate items
            foreach ($request->input('items', []) as $itemData) {
                $calculatedItem = $this->calculator->calculateItemData($itemData);
                
                $item = QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'service_id' => $calculatedItem['service_id'] ?? null,
                    'service_item_id' => $calculatedItem['service_item_id'] ?? null,
                    'manual_service_name' => $calculatedItem['manual_service_name'] ?? null,
                    'manual_item_name' => $calculatedItem['manual_item_name'] ?? null,
                    'description' => $calculatedItem['description'] ?? null,
                    'unit' => $calculatedItem['unit'],
                    'quantity' => $calculatedItem['quantity'],
                    'base_cost' => $calculatedItem['base_cost'] ?? 0,
                    'margin_type' => $calculatedItem['margin_type'],
                    'margin_value' => $calculatedItem['margin_value'],
                    'selling_rate' => $calculatedItem['selling_rate'],
                    'gst_percentage' => $calculatedItem['gst_percentage'],
                    'line_total' => $calculatedItem['line_total']
                ]);

                if (!empty($itemData['vendor_id']) && !empty($itemData['vendor_rate'])) {
                    QuotationVendorCost::create([
                        'quotation_item_id' => $item->id,
                        'vendor_id' => $itemData['vendor_id'],
                        'vendor_rate' => $itemData['vendor_rate'],
                        'vendor_total' => ($itemData['vendor_rate'] * $itemData['quantity']),
                        'vendor_gst' => 0
                    ]);
                }
            }

            // Recalculate totals
            $quotation = $this->calculator->recalculateQuotation($quotation);

            DB::commit();
            return $this->success($quotation->load('items', 'company', 'enquiry'), 'Quotation updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => 'Failed to update quotation: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();
        return $this->success([], 'Quotation deleted successfully');
    }

    public function revise($id)
    {
        $original = Quotation::with('items.vendorCost')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Clone quotation
            $revised = $original->replicate();
            $revised->parent_quotation_id = $original->id;
            $revised->revision_no = $original->revision_no + 1;
            $revised->status = 'DRAFT';
            $revised->quotation_no = $this->generateQuotationNumber($original->company_id);
            $revised->created_by = Auth::id();
            $revised->save();

            // Clone items
            foreach ($original->items as $item) {
                $newItem = $item->replicate();
                $newItem->quotation_id = $revised->id;
                $newItem->save();

                // Clone vendor cost if exists
                if ($item->vendorCost) {
                    $newVendorCost = $item->vendorCost->replicate();
                    $newVendorCost->quotation_item_id = $newItem->id;
                    $newVendorCost->save();
                }
            }

            // Mark original as REVISED
            $original->update(['status' => 'REVISED']);

            DB::commit();
            return $this->success($revised->load('items', 'company', 'enquiry'), 'Quotation revised successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation revision failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to revise quotation'], 500);
        }
    }

    public function nextNumber(Request $request)
    {
        $companyId = $request->input('company_id');
        if (!$companyId) {
            return response()->json(['status' => 'error', 'message' => 'company_id required'], 400);
        }

        $quotationNo = $this->generateQuotationNumber($companyId);
        return $this->success(['quotation_no' => $quotationNo]);
    }

    public function getServiceItems(Request $request)
    {
        $serviceId = $request->input('service_id');
        if (!$serviceId) {
            return response()->json(['status' => 'error', 'message' => 'service_id required'], 400);
        }

        $items = ServiceItem::with('unitMaster')
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get();

        return $this->success($items);
    }

    public function calculateMargin(Request $request)
    {
        $baseCost = (float) $request->input('base_cost', 0);
        $marginType = $request->input('margin_type', 'PERCENTAGE');
        $marginValue = (float) $request->input('margin_value', 0);

        $sellingRate = $this->calculator->calculateSellingRate($baseCost, $marginType, $marginValue);

        return $this->success(['selling_rate' => $sellingRate]);
    }

    public function pdf($id)
    {
        $quotation = Quotation::with([
            'enquiry',
            'company',
            'termsCondition',
            'items.service',
            'items.serviceItem',
            'createdBy'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('quotations.quotation-pdf', compact('quotation'))
            ->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $filename = ($quotation->quotation_no ?: 'quotation-') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Generate quotation number using company prefix + FY + sequence
     */
    private function generateQuotationNumber($companyId)
    {
        $company = Company::find($companyId);
        $prefix = ($company && $company->quotation_prefix) ? $company->quotation_prefix : 'QT-';

        // Determine financial year code
        $now = now();
        $yearStart = $now->month >= 4 ? $now->year : $now->year - 1;
        $fySuffix = substr((string)$yearStart, 2, 2) . substr((string)($yearStart + 1), 2, 2);

        // Find last quotation for this company in this FY
        $like = $prefix . $fySuffix . '%';
        $last = Quotation::where('company_id', $companyId)
            ->where('quotation_no', 'like', $like)
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($last) {
            $base = $prefix . $fySuffix;
            if (strpos($last->quotation_no, $base) === 0) {
                $suffixPart = substr($last->quotation_no, strlen($base));
                if (preg_match('/^0*(\d+)$/', $suffixPart, $mm)) {
                    $nextSeq = intval($mm[1]) + 1;
                }
            }
        }

        $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
        $generated = $prefix . $fySuffix . $seqStr;

        // Ensure uniqueness
        $tries = 0;
        while (Quotation::where('quotation_no', $generated)->exists() && $tries < 5) {
            $tries++;
            $nextSeq++;
            $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
            $generated = $prefix . $fySuffix . $seqStr;
        }

        return $generated;
    }
}
