<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = "Categories";
        if ($request->wantsJson()) {
            $data = Category::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.category.index', compact('heading'));
    }

    public function create()
    {
        $heading = "Create Category";
        return view('master.category.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $category = Category::create(['name' => $request->input('name')]);
        return $this->success($category, 'Category created successfully');
    }

    public function edit($id)
    {
        $heading = "Edit Category";
        $category = Category::findOrFail($id);
        return view('master.category.edit', compact('category', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $category->update(['name' => $request->input('name')]);
        return $this->success($category, 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return $this->success([], 'Category deleted successfully');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $normalQuery = Category::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $normalQuery->where('id', '!=', $excludeId);
        $normalExists = $normalQuery->exists();
        if ($normalExists) {
            return response()->json(['exists' => true, 'trashed' => false]);
        }

        $trashedQuery = Category::onlyTrashed()->where('name', $name);
        if ($excludeId) $trashedQuery->where('id', '!=', $excludeId);
        $trashed = $trashedQuery->first();
        if ($trashed) {
            return response()->json(['exists' => true, 'trashed' => true, 'id' => $trashed->id]);
        }

        return response()->json(['exists' => false]);
    }

    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return $this->success($category, 'Category restored successfully');
    }
}
