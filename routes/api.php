<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('users/export', [ExcelController::class, 'export'])->name('users.export');
    Route::post('users/import', [ExcelController::class, 'import'])->name('users.import');
    Route::get('users/pdf', [PdfController::class, 'users'])->name('users.pdf');
    Route::post('users/{user}/media/upload', [MediaController::class, 'upload'])->name('users.media.upload');
    Route::get('users/{user}/media', [MediaController::class, 'list'])->name('users.media.list');
    Route::delete('users/{user}/media/{mediaId}', [MediaController::class, 'delete'])->name('users.media.delete');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs.index');
    Route::get('users/{user}/activity-logs', [ActivityLogController::class, 'user'])->name('activity.logs.user');
});