<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class SupplierController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Suppliers';
        if ($request->wantsJson()) {
            $q = Supplier::query();
            if ($request->query('trashed')) {
                $q = Supplier::onlyTrashed();
            }
            $data = $q->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.supplier.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Supplier';
        return view('master.supplier.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'alternate_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'gst_no' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

    $data = $request->only(['name','contact_person','mobile','alternate_number','location','email','address_line1','address_line2','city','pincode','gst_no']);
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['logo'] = 'assets/images/uploads/' . $filename;
        }

        $supplier = Supplier::create($data);
        return $this->success($supplier, 'Supplier created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Supplier';
        $supplier = Supplier::findOrFail($id);
        return view('master.supplier.edit', compact('supplier','heading'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:suppliers,name,'.$supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'alternate_number' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'gst_no' => 'nullable|string|max:100'
        ]);
        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

    $data = $request->only(['name','contact_person','mobile','alternate_number','location','email','address_line1','address_line2','city','pincode','gst_no']);
        if ($request->hasFile('logo')) {
            if ($supplier->logo && File::exists(public_path($supplier->logo))) { try { File::delete(public_path($supplier->logo)); } catch (\Exception $e) { } }
            $file = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['logo'] = 'assets/images/uploads/' . $filename;
        }

        $supplier->update($data);
        return $this->success($supplier, 'Supplier updated successfully');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return $this->success([], 'Supplier deleted successfully');
    }

    public function forceDelete($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        if ($supplier->logo && File::exists(public_path($supplier->logo))) { try { File::delete(public_path($supplier->logo)); } catch (\Exception $e) { } }
        $supplier->forceDelete();
        return $this->success([], 'Supplier permanently deleted');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name'); $excludeId = $request->input('exclude_id');
        $normalQuery = Supplier::where('name', $name)->whereNull('deleted_at'); if ($excludeId) $normalQuery->where('id','!=',$excludeId);
        if ($normalQuery->exists()) return response()->json(['exists'=>true,'trashed'=>false]);
        $trashed = Supplier::onlyTrashed()->where('name',$name)->when($excludeId, function($q) use($excludeId){ $q->where('id','!=',$excludeId); })->first();
        if ($trashed) return response()->json(['exists'=>true,'trashed'=>true,'id'=>$trashed->id]);
        return response()->json(['exists'=>false]);
    }

    public function restore($id)
    {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();
        return $this->success($supplier,'Supplier restored successfully');
    }
}
