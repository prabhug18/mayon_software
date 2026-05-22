<?php

namespace App\Http\Controllers;

use App\Models\Methodology;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class MethodologyController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Methodology';
        if ($request->wantsJson()) {
            $data = Methodology::orderBy('title')->get();
            return $this->success($data);
        }
        return view('master.methodology.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Methodology';
        return view('master.methodology.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'applicable_for' => 'required|in:flooring,civil,fabrication,networking,all,quotation,invoice,both',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $methodology = Methodology::create($request->all());
        return $this->success($methodology, 'Methodology created successfully');
    }

    public function show($id)
    {
        $methodology = Methodology::findOrFail($id);
        return $this->success($methodology);
    }

    public function edit($id)
    {
        $heading = 'Edit Methodology';
        $methodology = Methodology::findOrFail($id);
        return view('master.methodology.edit', compact('heading', 'methodology'));
    }

    public function update(Request $request, $id)
    {
        $methodology = Methodology::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'applicable_for' => 'required|in:flooring,civil,fabrication,networking,all,quotation,invoice,both',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $methodology->update($request->all());
        return $this->success($methodology, 'Methodology updated successfully');
    }

    public function destroy($id)
    {
        $methodology = Methodology::findOrFail($id);
        $methodology->delete();
        return $this->success([], 'Methodology deleted successfully');
    }
}
