<?php

namespace App\Http\Controllers;

use App\Models\EnquiryType;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;

class EnquiryTypeController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Enquiry Types';
        if ($request->wantsJson()) {
            $data = EnquiryType::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.enquiryType.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Enquiry Type';
        return view('master.enquiryType.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:enquiry_types,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $et = EnquiryType::create(['name' => $request->input('name')]);
        return $this->success($et, 'Enquiry Type created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Enquiry Type';
        $et = EnquiryType::findOrFail($id);
        return view('master.enquiryType.edit', compact('et', 'heading'));
    }

    public function update(Request $request, $id)
    {
        $et = EnquiryType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:enquiry_types,name,' . $et->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $et->update(['name' => $request->input('name')]);
        return $this->success($et, 'Enquiry Type updated successfully');
    }

    public function destroy($id)
    {
        $et = EnquiryType::findOrFail($id);
        $et->delete();
        return $this->success([], 'Enquiry Type deleted successfully');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name');
        $excludeId = $request->input('exclude_id');

        $normalQuery = EnquiryType::where('name', $name)->whereNull('deleted_at');
        if ($excludeId) $normalQuery->where('id', '!=', $excludeId);
        if ($normalQuery->exists()) return response()->json(['exists' => true, 'trashed' => false]);

        $trashedQuery = EnquiryType::onlyTrashed()->where('name', $name);
        if ($excludeId) $trashedQuery->where('id', '!=', $excludeId);
        $trashed = $trashedQuery->first();
        if ($trashed) return response()->json(['exists' => true, 'trashed' => true, 'id' => $trashed->id]);

        return response()->json(['exists' => false]);
    }

    public function restore($id)
    {
        $et = EnquiryType::onlyTrashed()->findOrFail($id);
        $et->restore();
        return $this->success($et, 'Enquiry Type restored successfully');
    }
}
