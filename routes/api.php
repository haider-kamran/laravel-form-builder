<?php

use Illuminate\Support\Facades\Route;
use Hyderkamran\FormBuilder\Http\Controllers\FormController;

Route::prefix('api/form-builder')->middleware('api')->group(function () {
    Route::get('/forms/{slug}', [FormController::class, 'show']);
    Route::post('/forms/{slug}/submit', [FormController::class, 'submit']);
});
