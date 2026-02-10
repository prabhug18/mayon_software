<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrderItem;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Purchase Orders';
        if ($request->wantsJson()) {
            $data = PurchaseOrder::with(['supplier','project'])->orderBy('id','desc')->get();
            return $this->success($data);
        }
        return view('module.purchaseOrder.index', compact('heading'));
    }

    public function show($id)
    {
        $heading = 'Purchase Order Details';
        // eager-load company so view can access company fields without extra queries
    $po = PurchaseOrder::with(['items.uom','supplier','project','siteEngineer','company'])->findOrFail($id);
        return view('module.purchaseOrder.show', compact('po','heading'));
    }

    public function create()
    {
        $heading = 'Add Purchase Order';
        $projects = Project::orderBy('name')->get();
        $companies = \App\Models\Company::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();
        return view('module.purchaseOrder.create', compact('heading','projects','companies','users'));
    }

    public function store(Request $request)
    {
    $validator = Validator::make($request->all(), [
            // allow po_number to be omitted; we'll auto-generate based on company prefix + financial year + sequence
            'po_number' => 'nullable|string|unique:purchase_orders,po_number',
            'po_date' => 'nullable|date',
            'company_id' => 'required|exists:companies,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'required|exists:projects,id',
            'site_engineer_id' => 'required|exists:users,id',
            'amount' => 'nullable|numeric',
            'status' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            // unit_price and total are optional: users may omit them
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
            // optional product_id (nullable integer referencing products) and uom (nullable string)
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.uom_id' => 'nullable|integer|exists:uoms,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::info('PO store validation failed', ['input' => $request->all(), 'errors' => $validator->errors()->toArray()]);
            $resp = ['status'=>'error','errors'=>$validator->errors()];
            if (app()->isLocal()) $resp['input'] = $request->all();
            return response()->json($resp,422);
        }

    // prefer explicit po_date if provided, otherwise accept legacy 'date'
    $data = $request->only(['po_number','po_date','company_id','supplier_id','project_id','site_engineer_id','amount','status','notes']);
        $data['created_by'] = auth()->id();

        // If po_number not supplied, generate using company->po_prefix + financial year code + sequence per company
        if (empty($data['po_number'])) {
            try {
                $company = \App\Models\Company::find($data['company_id']);
                $prefix = ($company && $company->po_prefix) ? $company->po_prefix : 'PO-';

                // Determine financial year code, e.g., 2025-2026 -> '2526'
                $now = now();
                $yearStart = $now->month >= 4 ? $now->year : $now->year - 1; // FY starts Apr
                $fySuffix = substr((string)$yearStart, 2, 2) . substr((string)($yearStart + 1), 2, 2);

                // Find last PO for this company in this FY (matching prefix+fySuffix)
                $like = $prefix . $fySuffix . '%';
                $last = PurchaseOrder::where('company_id', $data['company_id'])
                        ->where('po_number', 'like', $like)
                        ->orderBy('id', 'desc')
                        ->first();

                $nextSeq = 1;
                if ($last) {
                    // attempt to strip the known prefix + fySuffix and parse only the sequence portion
                    $base = $prefix . $fySuffix;
                    $suffixPart = '';
                    if (strpos($last->po_number, $base) === 0) {
                        $suffixPart = substr($last->po_number, strlen($base));
                        if (preg_match('/^0*(\d+)$/', $suffixPart, $mm)) {
                            $nextSeq = intval($mm[1]) + 1;
                        } else {
                            // fallback to trailing digits
                            if (preg_match('/(\d+)$/', $last->po_number, $mm2)) {
                                $nextSeq = intval($mm2[1]) + 1;
                            }
                        }
                    } else {
                        // last PO didn't start with expected base; fallback to trailing digits
                        if (preg_match('/(\d+)$/', $last->po_number, $mm2)) {
                            $nextSeq = intval($mm2[1]) + 1;
                        }
                    }
                }
                // use minimum 2-digit padding for sequence (01,02,...,99,100,101...)
                $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
                // concatenate without a slash as requested
                $generated = $prefix . $fySuffix . $seqStr;

                // ensure uniqueness (small retry loop)
                $tries = 0;
                while (PurchaseOrder::where('po_number', $generated)->exists() && $tries < 5) {
                    $tries++;
                    $nextSeq++;
                    $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
                    // concatenate without a slash as requested
                    $generated = $prefix . $fySuffix . $seqStr;
                }
                $data['po_number'] = $generated;
            } catch (\Exception $e) {
                // fallback to timestamp-based PO if anything goes wrong
                $now = now();
                $data['po_number'] = 'PO-' . $now->format('YmdHis');
                Log::warning('PO generation fallback used: '.$e->getMessage());
            }
        }

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::create($data);
            // create items
            foreach ($request->input('items', []) as $it) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'description' => $it['description'] ?? null,
                    'quantity' => $it['quantity'] ?? 1,
                    // store NULL when not provided instead of forcing 0
                    'unit_price' => array_key_exists('unit_price', $it) && $it['unit_price'] !== '' ? $it['unit_price'] : null,
                    'total' => array_key_exists('total', $it) && $it['total'] !== '' ? $it['total'] : null,
                    'product_id' => $it['product_id'] ?? null,
                    'uom_id' => $it['uom_id'] ?? null,
                ]);
            }
            DB::commit();
            return $this->success($po->load('items','supplier','project','company'),'Purchase Order created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO store failed: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            if (app()->isLocal()) {
                return response()->json(['status'=>'error','message'=>'Failed to create purchase order: '.$e->getMessage(),'trace'=>$e->getTraceAsString()],500);
            }
            return $this->error('Failed to create purchase order',500);
        }
    }


    public function edit($id)
    {
    $heading = 'Edit Purchase Order';
    $po = PurchaseOrder::findOrFail($id);
    $projects = Project::orderBy('name')->get();
    $companies = \App\Models\Company::orderBy('name')->get();
    $users = \App\Models\User::orderBy('name')->get();
    return view('module.purchaseOrder.edit', compact('po','heading','projects','companies','users'));
    }

    public function update(Request $request, $id)
    {
    $po = PurchaseOrder::findOrFail($id);
    $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|unique:purchase_orders,po_number,' . $po->id,
            'date' => 'nullable|date',
            'company_id' => 'required|exists:companies,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'required|exists:projects,id',
            'site_engineer_id' => 'required|exists:users,id',
            'amount' => 'nullable|numeric',
            'status' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.uom_id' => 'nullable|integer|exists:uoms,id',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::info('PO update validation failed', ['input' => $request->all(), 'errors' => $validator->errors()->toArray(), 'po_id'=>$po->id]);
            $resp = ['status'=>'error','errors'=>$validator->errors()];
            if (app()->isLocal()) $resp['input'] = $request->all();
            return response()->json($resp,422);
        }

        $data = $request->only(['po_number','date','company_id','supplier_id','project_id','site_engineer_id','amount','status','notes']);

        DB::beginTransaction();
        try {
            $po->update($data);
            // replace items
            PurchaseOrderItem::where('purchase_order_id', $po->id)->delete();
            foreach ($request->input('items', []) as $it) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'description' => $it['description'] ?? null,
                    'quantity' => $it['quantity'] ?? 1,
                    'unit_price' => array_key_exists('unit_price', $it) && $it['unit_price'] !== '' ? $it['unit_price'] : null,
                    'total' => array_key_exists('total', $it) && $it['total'] !== '' ? $it['total'] : null,
                    'product_id' => $it['product_id'] ?? null,
                    'uom_id' => $it['uom_id'] ?? null,
                ]);
            }
            DB::commit();
            return $this->success($po->load('items','supplier','project','company'), 'Purchase Order updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO update failed: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            if (app()->isLocal()) {
                return response()->json(['status'=>'error','message'=>'Failed to update purchase order: '.$e->getMessage(),'trace'=>$e->getTraceAsString()],500);
            }
            return $this->error('Failed to update purchase order',500);
        }
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->delete();
        return $this->success([], 'Purchase Order deleted successfully');
    }

    // Supplier autosuggest endpoint
    public function supplierSearch(Request $request)
    {
        $q = $request->input('q');
        if (! $q || strlen($q) < 3) return response()->json([]);
        // search across name, contact_person, mobile
        $suppliers = Supplier::where(function($qbf) use ($q) {
                $qbf->where('name', 'like', '%'.$q.'%')
                    ->orWhere('contact_person', 'like', '%'.$q.'%')
                    ->orWhere('mobile', 'like', '%'.$q.'%');
            })
            ->limit(10)
            ->get(['id','name','contact_person','mobile','email','gst_no','location','address_line1','address_line2','city','pincode']);
        return $this->success($suppliers);
    }

    // Return next available PO number for a company (used by frontend to preview)
    public function nextNumber(Request $request)
    {
        $companyId = $request->input('company_id');
        if (! $companyId) return response()->json(['status'=>'error','message'=>'company_id required'], 400);
        $company = \App\Models\Company::find($companyId);
        $prefix = ($company && $company->po_prefix) ? $company->po_prefix : 'PO-';

        $now = now();
        $yearStart = $now->month >= 4 ? $now->year : $now->year - 1;
        $fySuffix = substr((string)$yearStart, 2, 2) . substr((string)($yearStart + 1), 2, 2);

        $like = $prefix . $fySuffix . '%';
        $last = PurchaseOrder::where('company_id', $companyId)
                ->where('po_number', 'like', $like)
                ->orderBy('id', 'desc')
                ->first();

        $nextSeq = 1;
        if ($last) {
            $base = $prefix . $fySuffix;
            $suffixPart = '';
            if (strpos($last->po_number, $base) === 0) {
                $suffixPart = substr($last->po_number, strlen($base));
                if (preg_match('/^0*(\d+)$/', $suffixPart, $mm)) {
                    $nextSeq = intval($mm[1]) + 1;
                } else {
                    if (preg_match('/(\d+)$/', $last->po_number, $mm2)) {
                        $nextSeq = intval($mm2[1]) + 1;
                    }
                }
            } else {
                if (preg_match('/(\d+)$/', $last->po_number, $mm2)) {
                    $nextSeq = intval($mm2[1]) + 1;
                }
            }
        }
        // minimum 2-digit padding (01,02,...)
        $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
        // concatenate without a slash to match requested format e.g. Ko252601
        $generated = $prefix . $fySuffix . $seqStr;
        return $this->success(['po_number' => $generated]);
    }

    // Generate PDF for a purchase order
    public function pdf($id)
    {
    $po = PurchaseOrder::with(['items.uom','supplier','project','siteEngineer','company','createdBy'])->findOrFail($id);

        // Try Puppeteer (headless Chrome) for pixel-perfect rendering if node + puppeteer present.
        // Build absolute URL to the show page which should render like the printed view
        try {
            // Prefer absolute route URL (uses app.url or request host as configured)
            // Route parameter is named 'purchaseOrder' (model binding), pass it exactly
            $showUrl = route('purchaseOrders.show', ['purchaseOrder' => $po->id]);

            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'po_' . $po->id . '_' . time() . '.pdf';

            // Node script path: prefer .cjs to avoid ES module 'require' error when project uses "type": "module"
            $nodeScriptCjs = base_path('tools/puppeteer-render.cjs');
            $nodeScriptJs = base_path('tools/puppeteer-render.js');
            $nodeScript = file_exists($nodeScriptCjs) ? $nodeScriptCjs : $nodeScriptJs;
            if (file_exists($nodeScript)) {
                // Build command: prefer 'node' binary; do not rely on PHP_BINARY
                $nodeBin = 'node';
                $cmd = $nodeBin . ' ' . escapeshellarg($nodeScript) . ' ' . escapeshellarg($showUrl) . ' ' . escapeshellarg($tmp);
                $output = [];
                $returnVar = null;
                exec($cmd . ' 2>&1', $output, $returnVar);

                // Log Puppeteer attempt details for diagnostics
                Log::info('Puppeteer render attempted', ['cmd' => $cmd, 'return' => $returnVar, 'output' => $output]);

                if ($returnVar === 0 && file_exists($tmp)) {
                    return response()->download($tmp, ($po->po_number ?: 'purchase-order-') . '.pdf')->deleteFileAfterSend(true);
                }
                // else fall through to dompdf
            } else {
                Log::info('Puppeteer script not found; looked for: ' . $nodeScriptCjs . ' and ' . $nodeScriptJs);
            }
        } catch (\Exception $e) {
            Log::warning('Puppeteer render failed: ' . $e->getMessage(), ['exception' => $e]);
        }

        // Fallback to DOMPDF
        $pdf = Pdf::loadView('module.purchaseOrder.pdf', compact('po'))
            ->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $filename = ($po->po_number ?: 'purchase-order-') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Browser print preview: render the PDF blade as HTML so browser print matches PDF output.
     * Use ?autoprint=1 to automatically call window.print() when opened in a new tab.
     */
    public function print($id, Request $request)
    {
    $po = PurchaseOrder::with(['items.uom','supplier','project','siteEngineer','company','createdBy'])->findOrFail($id);
        $autoprint = $request->query('autoprint') == '1';
        // Debug log to confirm this route is hit and whether autoprint was requested
        Log::info('Print preview requested', ['purchase_order_id' => $id, 'autoprint' => $autoprint, 'user_id' => auth()->id()]);
        return view('module.purchaseOrder.pdf', compact('po','autoprint'));
    }

    /**
     * Send invoice PDF to supplier email and CC company email
     */
    public function sendInvoice($id, Request $request)
    {
        $po = PurchaseOrder::with(['items.uom','supplier','project','siteEngineer','company','createdBy'])->findOrFail($id);

        $supplierEmail = optional($po->supplier)->email;
        $companyEmail = optional($po->company)->email;

        if (! $supplierEmail) {
            return $this->error('Supplier has no email address configured', 422);
        }

        try {
            $mailable = new \App\Mail\PurchaseOrderInvoice($po);
            $mail = \Mail::to($supplierEmail);
            if ($companyEmail) $mail = $mail->cc($companyEmail);
            $mail->send($mailable);
            return $this->success([], 'Invoice sent to supplier');
        } catch (\Exception $e) {
            Log::error('Failed to send PO invoice email: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->error('Failed to send invoice email', 500);
        }
    }
}
