<?php

namespace App\Http\Controllers;

use App\Models\ServiceItem;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class ServiceItemController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Service Items';
        if ($request->wantsJson()) {
            $data = ServiceItem::with(['service', 'unitMaster'])->orderBy('item_name')->get();
            return $this->success($data);
        }
        return view('master.service-items.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Service Item';
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        return view('master.service-items.create', compact('heading', 'services', 'units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hsn_sac_code' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'default_rate' => 'nullable|numeric|min:0',
            'is_optional' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $serviceItem = ServiceItem::create($request->all());
        return $this->success($serviceItem->load('service'), 'Service item created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Service Item';
        $serviceItem = ServiceItem::findOrFail($id);
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        return view('master.service-items.create', compact('heading', 'serviceItem', 'services', 'units'));
    }

    public function update(Request $request, $id)
    {
        $serviceItem = ServiceItem::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hsn_sac_code' => 'nullable|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'default_rate' => 'nullable|numeric|min:0',
            'is_optional' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $serviceItem->update($request->all());
        return $this->success($serviceItem->load('service'), 'Service item updated successfully');
    }

    public function destroy($id)
    {
        $serviceItem = ServiceItem::findOrFail($id);
        $serviceItem->delete();
        return $this->success([], 'Service item deleted successfully');
    }
}
