<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class UomController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'UOMs';
        if ($request->wantsJson()) {
            $data = Uom::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.uom.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add UOM';
        return view('master.uom.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:uoms,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $uom = Uom::create(['name' => $request->input('name')]);
        return $this->success($uom, 'UOM created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit UOM';
        $uom = Uom::findOrFail($id);
        return view('master.uom.edit', compact('uom', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $uom = Uom::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:uoms,name,' . $uom->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $uom->update(['name' => $request->input('name')]);
        return $this->success($uom, 'UOM updated successfully');
    }

    public function destroy($id)
    {
        $uom = Uom::findOrFail($id);
        $uom->delete();
        return $this->success([], 'UOM deleted successfully');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $normalQuery = Uom::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $normalQuery->where('id', '!=', $excludeId);
        if ($normalQuery->exists()) return response()->json(['exists' => true, 'trashed' => false]);

        $trashedQuery = Uom::onlyTrashed()->where('name', $name);
        if ($excludeId) $trashedQuery->where('id', '!=', $excludeId);
        $trashed = $trashedQuery->first();
        if ($trashed) return response()->json(['exists' => true, 'trashed' => true, 'id' => $trashed->id]);

        return response()->json(['exists' => false]);
    }

    public function restore($id)
    {
        $uom = Uom::onlyTrashed()->findOrFail($id);
        $uom->restore();
        return $this->success($uom, 'UOM restored successfully');
    }
}
