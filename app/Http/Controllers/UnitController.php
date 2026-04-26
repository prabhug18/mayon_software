<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Units';
        if ($request->wantsJson()) {
            $data = Unit::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.unit.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Unit';
        return view('master.unit.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $unit = Unit::create(['name' => $request->input('name')]);
        return $this->success($unit, 'Unit created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Unit';
        $unit = Unit::findOrFail($id);
        return view('master.unit.create', compact('unit', 'heading')); // Reusing create view for edit as it's simple
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $unit->update(['name' => $request->input('name')]);
        return $this->success($unit, 'Unit updated successfully');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return $this->success([], 'Unit deleted successfully');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $query = Unit::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $query->where('id', '!=', $excludeId);
        
        if ($query->exists()) return response()->json(['exists' => true]);

        return response()->json(['exists' => false]);
    }
}
