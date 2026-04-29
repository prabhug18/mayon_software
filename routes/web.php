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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User & Role Management
    Route::middleware(['permission:manage users'])->group(function () {
        Route::get('users/export', [ExcelController::class, 'export'])->name('users.export');
        Route::post('users/import', [ExcelController::class, 'import'])->name('users.import');
        Route::get('users/pdf', [PdfController::class, 'users'])->name('users.pdf');
        Route::resource('users', UserController::class);
        Route::post('users/{user}/media/upload', [MediaController::class, 'upload'])->name('users.media.upload');
        Route::get('users/{user}/media', [MediaController::class, 'list'])->name('users.media.list');
        Route::delete('users/{user}/media/{mediaId}', [MediaController::class, 'delete'])->name('users.media.delete');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index');
        Route::get('users/{user}/activity-logs', [ActivityLogController::class, 'user'])->name('activity.logs.user');
    });

    Route::middleware(['permission:manage roles'])->group(function () {
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });

    // Master Data / Settings
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::resource('sources', App\Http\Controllers\SourceController::class);
        Route::post('sources/check-name', [App\Http\Controllers\SourceController::class, 'checkName'])->name('sources.checkName');
        Route::post('sources/{id}/restore', [App\Http\Controllers\SourceController::class, 'restore'])->name('sources.restore');
        Route::resource('categories', App\Http\Controllers\CategoryController::class);
        Route::post('categories/check-name', [App\Http\Controllers\CategoryController::class, 'checkName'])->name('categories.checkName');
        Route::post('categories/{id}/restore', [App\Http\Controllers\CategoryController::class, 'restore'])->name('categories.restore');
        Route::resource('enquiry-types', App\Http\Controllers\EnquiryTypeController::class);
        Route::post('enquiry-types/check-name', [App\Http\Controllers\EnquiryTypeController::class, 'checkName'])->name('enquiry-types.checkName');
        Route::post('enquiry-types/{id}/restore', [App\Http\Controllers\EnquiryTypeController::class, 'restore'])->name('enquiry-types.restore');
        Route::resource('uoms', App\Http\Controllers\UomController::class);
        Route::post('uoms/check-name', [App\Http\Controllers\UomController::class, 'checkName'])->name('uoms.checkName');
        Route::post('uoms/{id}/restore', [App\Http\Controllers\UomController::class, 'restore'])->name('uoms.restore');
        Route::resource('master/units', App\Http\Controllers\UnitController::class)->names('units');
        Route::post('master/units/check-name', [App\Http\Controllers\UnitController::class, 'checkName'])->name('units.checkName');
        Route::resource('projects', App\Http\Controllers\ProjectController::class);
        Route::post('projects/check-name', [App\Http\Controllers\ProjectController::class, 'checkName'])->name('projects.checkName');
        Route::post('projects/{id}/restore', [App\Http\Controllers\ProjectController::class, 'restore'])->name('projects.restore');
        Route::delete('projects/{id}/force-delete', [App\Http\Controllers\ProjectController::class, 'forceDelete'])->name('projects.forceDelete');
        Route::resource('companies', App\Http\Controllers\CompanyController::class);
        Route::post('companies/check-name', [App\Http\Controllers\CompanyController::class, 'checkName'])->name('companies.checkName');
        Route::post('companies/{id}/restore', [App\Http\Controllers\CompanyController::class, 'restore'])->name('companies.restore');
        Route::delete('companies/{id}/force-delete', [App\Http\Controllers\CompanyController::class, 'forceDelete'])->name('companies.forceDelete');
    });

    // Supplier Management
    Route::middleware(['permission:view suppliers'])->group(function () {
        Route::resource('suppliers', App\Http\Controllers\SupplierController::class);
        Route::post('suppliers/check-name', [App\Http\Controllers\SupplierController::class, 'checkName'])->name('suppliers.checkName');
    });

    // Product Management
    Route::middleware(['permission:view products'])->group(function () {
        Route::get('products/search', [App\Http\Controllers\ProductController::class, 'search']);
        Route::resource('products', App\Http\Controllers\ProductController::class);
        Route::post('products/check-name', [App\Http\Controllers\ProductController::class, 'checkName'])->name('products.checkName');
    });

    Route::middleware(['permission:manage settings'])->group(function () {
        Route::resource('services', App\Http\Controllers\ServiceController::class);
        Route::resource('service-items', App\Http\Controllers\ServiceItemController::class);
        Route::resource('terms-conditions', App\Http\Controllers\TermsConditionController::class);
        Route::resource('vendors', App\Http\Controllers\VendorController::class);
    });

    // Enquiry Management
    Route::middleware(['permission:view enquiries'])->group(function () {
        Route::get('enquiries/import', [App\Http\Controllers\FacebookLeadImportController::class, 'showImportForm'])->name('enquiries.import');
        Route::post('enquiries/import/preview', [App\Http\Controllers\FacebookLeadImportController::class, 'preview'])->name('enquiries.import.preview');
        Route::post('enquiries/import/process', [App\Http\Controllers\FacebookLeadImportController::class, 'import'])->name('enquiries.import.process');

        Route::resource('enquiries', App\Http\Controllers\EnquiryController::class);
        Route::post('enquiries/check-name', [App\Http\Controllers\EnquiryController::class, 'checkName'])->name('enquiries.checkName');
        Route::post('enquiries/{id}/comments', [App\Http\Controllers\EnquiryController::class, 'storeComment'])->name('enquiries.comments.store');
        Route::delete('enquiries/{id}/comments/{comment}', [App\Http\Controllers\EnquiryController::class, 'destroyComment'])->name('enquiries.comments.destroy');
        Route::post('enquiries/{id}/follow-ups', [App\Http\Controllers\EnquiryController::class, 'storeFollowUp'])->name('enquiries.followups.store');
    });

    // Quotation Management
    Route::middleware(['permission:view quotations'])->group(function () {
        Route::get('quotations/next-number', [App\Http\Controllers\QuotationController::class, 'nextNumber'])->name('quotations.nextNumber');
        Route::post('quotations/{id}/revise', [App\Http\Controllers\QuotationController::class, 'revise'])->name('quotations.revise');
        Route::get('quotations/{id}/pdf', [App\Http\Controllers\QuotationController::class, 'pdf'])->name('quotations.pdf');
        Route::get('quotations/service-items', [App\Http\Controllers\QuotationController::class, 'getServiceItems'])->name('quotations.serviceItems');
        Route::post('quotations/calculate-margin', [App\Http\Controllers\QuotationController::class, 'calculateMargin'])->name('quotations.calculateMargin');
        Route::resource('quotations', App\Http\Controllers\QuotationController::class);
    });

    // Purchase Orders (module)
    Route::middleware(['permission:view purchase orders'])->group(function () {
        Route::get('purchaseOrders/suppliers/search', [App\Http\Controllers\Module\PurchaseOrderController::class, 'supplierSearch']);
        Route::get('purchaseOrders/next-number', [App\Http\Controllers\Module\PurchaseOrderController::class, 'nextNumber']);
        Route::get('purchaseOrders/{id}/pdf', [App\Http\Controllers\Module\PurchaseOrderController::class, 'pdf'])->name('purchaseOrders.pdf');
        Route::get('purchaseOrders/{id}/print', [App\Http\Controllers\Module\PurchaseOrderController::class, 'print'])->name('purchaseOrders.print');
        Route::post('purchaseOrders/{id}/send-invoice', [App\Http\Controllers\Module\PurchaseOrderController::class, 'sendInvoice'])->name('purchaseOrders.sendInvoice');
        Route::resource('purchaseOrders', App\Http\Controllers\Module\PurchaseOrderController::class);
    });

    // Dashboard AJAX routes for Modals
    Route::get('dashboard/enquiries/modal', [GeneralController::class, 'enquiriesList'])->name('dashboard.enquiries.modal');
    Route::get('dashboard/quotations/modal', [GeneralController::class, 'quotationsList'])->name('dashboard.quotations.modal');
    Route::get('dashboard/purchase-orders/modal', [GeneralController::class, 'purchaseOrdersList'])->name('dashboard.purchaseOrders.modal');
    Route::get('dashboard/followups/today', [GeneralController::class, 'todayFollowUps'])->name('dashboard.followups.today');
});

require __DIR__.'/auth.php';