<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ProjectController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Projects';
        if ($request->wantsJson()) {
            $data = Project::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.project.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Project';
        return view('master.project.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'logo_image' => 'nullable|image|max:2048',
            'status' => 'required|in:On Going,Completed'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'location', 'status', 'address']);
        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads');
            if (!File::exists($dest)) File::makeDirectory($dest, 0755, true);
            $file->move($dest, $filename);
            $data['logo_image'] = 'assets/images/uploads/' . $filename;
        }
        $project = Project::create($data);
        return $this->success($project, 'Project created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Project';
        $project = Project::findOrFail($id);
        return view('master.project.edit', compact('project', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'logo_image' => 'nullable|image|max:2048',
            'status' => 'required|in:On Going,Completed'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'location', 'status', 'address']);
        if ($request->hasFile('logo_image')) {
            // delete old file if exists
            if ($project->logo_image && File::exists(public_path($project->logo_image))) {
                try { File::delete(public_path($project->logo_image)); } catch (\Exception $e) { /* ignore */ }
            }
            $file = $request->file('logo_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads');
            if (!File::exists($dest)) File::makeDirectory($dest, 0755, true);
            $file->move($dest, $filename);
            $data['logo_image'] = 'assets/images/uploads/' . $filename;
        }
        $project->update($data);
        return $this->success($project, 'Project updated successfully');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        // soft delete only; do not delete logo file on soft delete
        $project->delete();
        return $this->success([], 'Project deleted successfully');
    }

    // permanently delete and remove logo file
    public function forceDelete($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        if ($project->logo_image && File::exists(public_path($project->logo_image))) {
            try { File::delete(public_path($project->logo_image)); } catch (\Exception $e) { /* ignore */ }
        }
        $project->forceDelete();
        return $this->success([], 'Project permanently deleted');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $normalQuery = Project::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $normalQuery->where('id', '!=', $excludeId);
        if ($normalQuery->exists()) return response()->json(['exists' => true, 'trashed' => false]);

        $trashedQuery = Project::onlyTrashed()->where('name', $name);
        if ($excludeId) $trashedQuery->where('id', '!=', $excludeId);
        $trashed = $trashedQuery->first();
        if ($trashed) return response()->json(['exists' => true, 'trashed' => true, 'id' => $trashed->id]);

        return response()->json(['exists' => false]);
    }

    public function restore($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();
        return $this->success($project, 'Project restored successfully');
    }
}
