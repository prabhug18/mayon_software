<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class CompanyController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Companies';
        if ($request->wantsJson()) {
            $data = Company::query()->orderBy('id', 'desc')->get();
            return $this->success($data);
        }
        return view('master.company.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Company';
        return view('master.company.create', compact('heading'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name',
            'contact_person' => 'nullable|string|max:255',
            'po_prefix' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'logo' => 'required|image|max:2048',
            'authorized_image' => 'nullable|image|max:2048',
            'gst_no' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

    $data = $request->only(['name','po_prefix','contact_person','mobile','email','address','gst_no']);
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['logo'] = 'assets/images/uploads/' . $filename;
        }
        if ($request->hasFile('authorized_image')) {
            $file = $request->file('authorized_image');
            $filename = time() . '_auth_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['authorized_image'] = 'assets/images/uploads/' . $filename;
        }

        $company = Company::create($data);
        return $this->success($company, 'Company created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Company';
        $company = Company::findOrFail($id);
        return view('master.company.edit', compact('company','heading'));
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:companies,name,'.$company->id,
            'contact_person' => 'nullable|string|max:255',
            'po_prefix' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|max:2048',
            'authorized_image' => 'nullable|image|max:2048',
            'gst_no' => 'nullable|string|max:100'
        ]);
        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

    $data = $request->only(['name','po_prefix','contact_person','mobile','email','address','gst_no']);
        if ($request->hasFile('logo')) {
            if ($company->logo && File::exists(public_path($company->logo))) { try { File::delete(public_path($company->logo)); } catch (\Exception $e) { } }
            $file = $request->file('logo');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['logo'] = 'assets/images/uploads/' . $filename;
        }
        if ($request->hasFile('authorized_image')) {
            if ($company->authorized_image && File::exists(public_path($company->authorized_image))) { try { File::delete(public_path($company->authorized_image)); } catch (\Exception $e) { } }
            $file = $request->file('authorized_image');
            $filename = time() . '_auth_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest, $filename);
            $data['authorized_image'] = 'assets/images/uploads/' . $filename;
        }

        $company->update($data);
        return $this->success($company, 'Company updated successfully');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        // soft delete only; do not delete logo file on soft delete
        $company->delete();
        return $this->success([], 'Company deleted successfully');
    }

    // permanently delete and remove logo file
    public function forceDelete($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        if ($company->logo && File::exists(public_path($company->logo))) { try { File::delete(public_path($company->logo)); } catch (\Exception $e) { } }
        $company->forceDelete();
        return $this->success([], 'Company permanently deleted');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name'); $excludeId = $request->input('exclude_id');
        $normalQuery = Company::where('name', $name)->whereNull('deleted_at'); if ($excludeId) $normalQuery->where('id','!=',$excludeId);
        if ($normalQuery->exists()) return response()->json(['exists'=>true,'trashed'=>false]);
        $trashed = Company::onlyTrashed()->where('name',$name)->when($excludeId, function($q) use($excludeId){ $q->where('id','!=',$excludeId); })->first();
        if ($trashed) return response()->json(['exists'=>true,'trashed'=>true,'id'=>$trashed->id]);
        return response()->json(['exists'=>false]);
    }

    public function restore($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();
        return $this->success($company,'Company restored successfully');
    }
}
