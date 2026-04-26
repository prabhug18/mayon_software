<?php

namespace App\Http\Controllers;

use App\Models\TermsCondition;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class TermsConditionController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Terms & Conditions';
        if ($request->wantsJson()) {
            $data = TermsCondition::orderBy('title')->get();
            return $this->success($data);
        }
        return view('master.terms-conditions.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Terms & Conditions';
        return view('master.terms-conditions.create', compact('heading'));
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

        $termsCondition = TermsCondition::create($request->all());
        return $this->success($termsCondition, 'Terms & Conditions created successfully');
    }

    public function show($id)
    {
        $termsCondition = TermsCondition::findOrFail($id);
        return $this->success($termsCondition);
    }

    public function edit($id)
    {
        $heading = 'Edit Terms & Conditions';
        $termsCondition = TermsCondition::findOrFail($id);
        return view('master.terms-conditions.edit', compact('heading', 'termsCondition'));
    }

    public function update(Request $request, $id)
    {
        $termsCondition = TermsCondition::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'applicable_for' => 'required|in:flooring,civil,fabrication,networking,all,quotation,invoice,both',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $termsCondition->update($request->all());
        return $this->success($termsCondition, 'Terms & Conditions updated successfully');
    }

    public function destroy($id)
    {
        $termsCondition = TermsCondition::findOrFail($id);
        $termsCondition->delete();
        return $this->success([], 'Terms & Conditions deleted successfully');
    }
}
