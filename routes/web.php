<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GeneralController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [GeneralController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('users/export', [ExcelController::class, 'export'])->name('users.export');
Route::post('users/import', [ExcelController::class, 'import'])->name('users.import');
Route::get('users/pdf', [PdfController::class, 'users'])->name('users.pdf');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);
    // Source resource (master data)
    Route::resource('sources', App\Http\Controllers\SourceController::class);
    Route::post('sources/check-name', [App\Http\Controllers\SourceController::class, 'checkName'])->name('sources.checkName');
    Route::post('sources/{id}/restore', [App\Http\Controllers\SourceController::class, 'restore'])->name('sources.restore');
    // Category resource (master data)
    Route::resource('categories', App\Http\Controllers\CategoryController::class);
    Route::post('categories/check-name', [App\Http\Controllers\CategoryController::class, 'checkName'])->name('categories.checkName');
    Route::post('categories/{id}/restore', [App\Http\Controllers\CategoryController::class, 'restore'])->name('categories.restore');
    // EnquiryType resource (master data)
    Route::resource('enquiry-types', App\Http\Controllers\EnquiryTypeController::class);
    Route::post('enquiry-types/check-name', [App\Http\Controllers\EnquiryTypeController::class, 'checkName'])->name('enquiry-types.checkName');
    Route::post('enquiry-types/{id}/restore', [App\Http\Controllers\EnquiryTypeController::class, 'restore'])->name('enquiry-types.restore');
    // UOM resource (master data)
    Route::resource('uoms', App\Http\Controllers\UomController::class);
    Route::post('uoms/check-name', [App\Http\Controllers\UomController::class, 'checkName'])->name('uoms.checkName');
    Route::post('uoms/{id}/restore', [App\Http\Controllers\UomController::class, 'restore'])->name('uoms.restore');
    // Project resource (master data)
    Route::resource('projects', App\Http\Controllers\ProjectController::class);
    Route::post('projects/check-name', [App\Http\Controllers\ProjectController::class, 'checkName'])->name('projects.checkName');
    Route::post('projects/{id}/restore', [App\Http\Controllers\ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('projects/{id}/force-delete', [App\Http\Controllers\ProjectController::class, 'forceDelete'])->name('projects.forceDelete');
    // Company resource (master data)
    Route::resource('companies', App\Http\Controllers\CompanyController::class);
    Route::post('companies/check-name', [App\Http\Controllers\CompanyController::class, 'checkName'])->name('companies.checkName');
    Route::post('companies/{id}/restore', [App\Http\Controllers\CompanyController::class, 'restore'])->name('companies.restore');
    Route::delete('companies/{id}/force-delete', [App\Http\Controllers\CompanyController::class, 'forceDelete'])->name('companies.forceDelete');
    // Supplier resource (master data)
    Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
    Route::post('suppliers/check-name', [App\Http\Controllers\SupplierController::class, 'checkName'])->name('suppliers.checkName');
    
    // Purchase Orders (module)
    Route::get('purchaseOrders/suppliers/search', [App\Http\Controllers\Module\PurchaseOrderController::class, 'supplierSearch']);
    // get next PO number for a company (used by front-end to show generated PO)
    Route::get('purchaseOrders/next-number', [App\Http\Controllers\Module\PurchaseOrderController::class, 'nextNumber']);
    // download PO PDF
    Route::get('purchaseOrders/{id}/pdf', [App\Http\Controllers\Module\PurchaseOrderController::class, 'pdf'])->name('purchaseOrders.pdf');
    // Print preview (browser) that renders the same PDF/print template and can auto-print
    Route::get('purchaseOrders/{id}/print', [App\Http\Controllers\Module\PurchaseOrderController::class, 'print'])->name('purchaseOrders.print');
    // Send invoice email with PDF attachment
    Route::post('purchaseOrders/{id}/send-invoice', [App\Http\Controllers\Module\PurchaseOrderController::class, 'sendInvoice'])->name('purchaseOrders.sendInvoice');
    Route::resource('purchaseOrders', App\Http\Controllers\Module\PurchaseOrderController::class);
    
    
    // Product resource (master data)
    Route::get('products/search', [App\Http\Controllers\ProductController::class, 'search']);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::post('products/check-name', [App\Http\Controllers\ProductController::class, 'checkName'])->name('products.checkName');

    // Enquiry resource
    Route::resource('enquiries', App\Http\Controllers\EnquiryController::class);
    Route::post('enquiries/check-name', [App\Http\Controllers\EnquiryController::class, 'checkName'])->name('enquiries.checkName');
    // Enquiry comments
    Route::post('enquiries/{id}/comments', [App\Http\Controllers\EnquiryController::class, 'storeComment'])->name('enquiries.comments.store');
    Route::delete('enquiries/{id}/comments/{comment}', [App\Http\Controllers\EnquiryController::class, 'destroyComment'])->name('enquiries.comments.destroy');
    // Enquiry follow-ups (history + create)
    Route::post('enquiries/{id}/follow-ups', [App\Http\Controllers\EnquiryController::class, 'storeFollowUp'])->name('enquiries.followups.store');
    // Enquiry comments
    Route::post('enquiries/{id}/comments', [App\Http\Controllers\EnquiryController::class, 'storeComment'])->name('enquiries.comments.store');
    Route::delete('enquiries/{id}/comments/{comment}', [App\Http\Controllers\EnquiryController::class, 'destroyComment'])->name('enquiries.comments.destroy');
    
    
    Route::post('users/{user}/media/upload', [MediaController::class, 'upload'])->name('users.media.upload');
    Route::get('users/{user}/media', [MediaController::class, 'list'])->name('users.media.list');
    Route::delete('users/{user}/media/{mediaId}', [MediaController::class, 'delete'])->name('users.media.delete');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index');
    Route::get('users/{user}/activity-logs', [ActivityLogController::class, 'user'])->name('activity.logs.user');
    // Dashboard follow-ups JSON (today)
    Route::get('dashboard/followups/today', [GeneralController::class, 'todayFollowUps'])->name('dashboard.followups.today');
});

require __DIR__.'/auth.php';