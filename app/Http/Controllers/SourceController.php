<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class SourceController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = "Sources";
        if ($request->wantsJson()) {
            $data = Source::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.source.index', compact('heading'));
    }

    public function create()
    {
        $heading = "Create Source";
        return view('master.source.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sources,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $source = Source::create(['name' => $request->input('name')]);
        return $this->success($source, 'Source created successfully');
    }

    public function edit($id)
    {
        $heading = "Edit Source";   
        $source = Source::findOrFail($id);
        return view('master.source.edit', compact('source', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $source = Source::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sources,name,' . $source->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $source->update(['name' => $request->input('name')]);
        return $this->success($source, 'Source updated successfully');
    }

    public function destroy($id)
    {
        $source = Source::findOrFail($id);
        $source->delete(); // soft delete
        return $this->success([], 'Source deleted successfully');
    }

    /**
     * Check if a source name already exists (AJAX)
     */
    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        // check not-deleted rows first
        $normalQuery = Source::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $normalQuery->where('id', '!=', $excludeId);
        $normalExists = $normalQuery->exists();
        if ($normalExists) {
            return response()->json(['exists' => true, 'trashed' => false]);
        }

        // check trashed rows
        $trashedQuery = Source::onlyTrashed()->where('name', $name);
        if ($excludeId) $trashedQuery->where('id', '!=', $excludeId);
        $trashed = $trashedQuery->first();
        if ($trashed) {
            return response()->json(['exists' => true, 'trashed' => true, 'id' => $trashed->id]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Restore a soft deleted source
     */
    public function restore($id)
    {
        $source = Source::onlyTrashed()->findOrFail($id);
        $source->restore();
        return $this->success($source, 'Source restored successfully');
    }
}
