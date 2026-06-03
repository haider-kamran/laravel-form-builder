<?php

use Illuminate\Support\Facades\Route;
use Hyderkamran\FormBuilder\Http\Controllers\FormController;
use Hyderkamran\FormBuilder\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {
    Route::post('/forms/{slug}/submit', [FormController::class, 'submit'])
         ->name('form-builder.submit');

    Route::get('/forms/{slug}/embed', [FormController::class, 'embed'])
         ->name('form-builder.embed');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('forms/admin')
     ->middleware(config('form-builder.admin_middleware', ['web']))
     ->name('form-builder.admin.')
     ->group(function () {

         // Forms CRUD
         Route::get('/',             [AdminController::class, 'index'])->name('index');
         Route::get('/create',       [AdminController::class, 'create'])->name('create');
         Route::post('/',            [AdminController::class, 'store'])->name('store');
         Route::get('/{id}/edit',    [AdminController::class, 'edit'])->name('edit');
         Route::put('/{id}',         [AdminController::class, 'update'])->name('update');
         Route::delete('/{id}',      [AdminController::class, 'destroy'])->name('destroy');

         // Submissions
         Route::get('/{id}/submissions', [AdminController::class, 'submissions'])->name('submissions');
         Route::delete('/submission/{submissionId}', [AdminController::class, 'destroySubmission'])
              ->name('submission.destroy');

         // Exports
         Route::get('/{id}/export/{format}', [AdminController::class, 'export'])
              ->name('export')
              ->where('format', 'csv|xlsx|pdf');

         // Notifications & Webhooks
         Route::post('/{id}/test-email',   [AdminController::class, 'testEmail'])->name('test-email');
         Route::post('/{id}/test-webhook', [AdminController::class, 'testWebhook'])->name('test-webhook');
     });
