<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Uom;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    use APIResponse;

    public function index(Request $request)
    {
        $heading = 'Products';
        if ($request->wantsJson()) {
            $q = Product::with(['category','uom'])->orderBy('id','desc');
            if ($request->query('trashed')) $q = Product::onlyTrashed();
            $data = $q->get();
            return $this->success($data);
        }
        return view('master.products.index', compact('heading'));
    }

    public function create()
    {
        $heading = 'Add Product';
        $categories = Category::orderBy('name')->get();
        $uoms = Uom::orderBy('name')->get();
        return view('master.products.create', compact('heading','categories','uoms'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'category_id' => 'nullable|exists:categories,id',
            'uom_id' => 'nullable|exists:uoms,id',
            'main_image' => 'nullable|image|max:4096'
        ]);
        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $data = $request->only(['name','category_id','uom_id']);
        if ($request->hasFile('main_image')) {
            $file = $request->file('main_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest,$filename);
            $data['main_image'] = 'assets/images/uploads/'.$filename;
        }

        $product = Product::create($data);
        return $this->success($product,'Product created successfully');
    }

    public function edit($id)
    {
        $heading = 'Edit Product';
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $uoms = Uom::orderBy('name')->get();
        return view('master.products.edit', compact('product','heading','categories','uoms'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name,'.$product->id,
            'category_id' => 'nullable|exists:categories,id',
            'uom_id' => 'nullable|exists:uoms,id',
            'main_image' => 'nullable|image|max:4096'
        ]);
        if ($validator->fails()) return response()->json(['status'=>'error','errors'=>$validator->errors()],422);

        $data = $request->only(['name','category_id','uom_id']);
        if ($request->hasFile('main_image')) {
            if ($product->main_image && File::exists(public_path($product->main_image))) { try{ File::delete(public_path($product->main_image)); } catch(\Exception $e){} }
            $file = $request->file('main_image');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/','_', $file->getClientOriginalName());
            $dest = public_path('assets/images/uploads'); if (!File::exists($dest)) File::makeDirectory($dest,0755,true);
            $file->move($dest,$filename);
            $data['main_image'] = 'assets/images/uploads/'.$filename;
        }

        $product->update($data);
        return $this->success($product,'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return $this->success([], 'Product deleted successfully');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        if ($product->main_image && File::exists(public_path($product->main_image))) { try{ File::delete(public_path($product->main_image)); } catch(\Exception $e){} }
        $product->forceDelete();
        return $this->success([], 'Product permanently deleted');
    }

    public function checkName(Request $request)
    {
        $name = $request->input('name'); $excludeId = $request->input('exclude_id');
        $normalQuery = Product::where('name',$name)->whereNull('deleted_at'); if ($excludeId) $normalQuery->where('id','!=',$excludeId);
        if ($normalQuery->exists()) return response()->json(['exists'=>true,'trashed'=>false]);
        $trashed = Product::onlyTrashed()->where('name',$name)->when($excludeId, function($q) use($excludeId){ $q->where('id','!=',$excludeId); })->first();
        if ($trashed) return response()->json(['exists'=>true,'trashed'=>true,'id'=>$trashed->id]);
        return response()->json(['exists'=>false]);
    }

    /**
     * Search products by name, category name or uom name. Returns JSON used by autosuggest.
     */
    public function search(Request $request)
    {
        $q = trim($request->query('q',''));
        if (!$q || strlen($q) < 1) return $this->success([], 'No query');
        $products = Product::with(['category','uom'])
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhereHas('category', function($cq) use ($q){ $cq->where('name','like', "%{$q}%"); })
                      ->orWhereHas('uom', function($uq) use ($q){ $uq->where('name','like', "%{$q}%"); });
            })->orderBy('name')->limit(30)->get();
        return $this->success($products);
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return $this->success($product,'Product restored successfully');
    }
}
