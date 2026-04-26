<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Services';
        if ($request->wantsJson()) {
            $data = Service::with('items')->orderBy('name')->get();
            return $this->success($data);
        }
        return view('master.services.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Service';
        return view('master.services.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'default_description' => 'nullable|string',
            'default_gst_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $service = Service::create($request->all());
        return $this->success($service, 'Service created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Service';
        $service = Service::findOrFail($id);
        return view('master.services.edit', compact('heading', 'service'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'default_description' => 'nullable|string',
            'default_gst_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $service->update($request->all());
        return $this->success($service, 'Service updated successfully');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return $this->success([], 'Service deleted successfully');
    }
}
