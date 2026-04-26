<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Vendors';
        if ($request->wantsJson()) {
            $data = Vendor::orderBy('name')->get();
            return $this->success($data);
        }
        return view('master.vendors.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Vendor';
        return view('master.vendors.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $vendor = Vendor::create($request->all());
        return $this->success($vendor, 'Vendor created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Vendor';
        $vendor = Vendor::findOrFail($id);
        return view('master.vendors.edit', compact('heading', 'vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $vendor->update($request->all());
        return $this->success($vendor, 'Vendor updated successfully');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return $this->success([], 'Vendor deleted successfully');
    }
}
