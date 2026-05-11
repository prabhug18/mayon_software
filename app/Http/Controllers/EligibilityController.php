<?php

namespace App\Http\Controllers;

use App\Models\Eligibility;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class EligibilityController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Eligibility';
        if ($request->wantsJson()) {
            $data = Eligibility::orderBy('title')->get();
            return $this->success($data);
        }
        return view('master.eligibility.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Eligibility';
        return view('master.eligibility.create', compact('heading'));
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

        $eligibility = Eligibility::create($request->all());
        return $this->success($eligibility, 'Eligibility created successfully');
    }

    public function show($id)
    {
        $eligibility = Eligibility::findOrFail($id);
        return $this->success($eligibility);
    }

    public function edit($id)
    {
        $heading = 'Edit Eligibility';
        $eligibility = Eligibility::findOrFail($id);
        return view('master.eligibility.edit', compact('heading', 'eligibility'));
    }

    public function update(Request $request, $id)
    {
        $eligibility = Eligibility::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'applicable_for' => 'required|in:flooring,civil,fabrication,networking,all,quotation,invoice,both',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $eligibility->update($request->all());
        return $this->success($eligibility, 'Eligibility updated successfully');
    }

    public function destroy($id)
    {
        $eligibility = Eligibility::findOrFail($id);
        $eligibility->delete();
        return $this->success([], 'Eligibility deleted successfully');
    }
}
