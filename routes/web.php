<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\DefaultController::class, 'healthz']);
Route::options('/{path}', [\App\Http\Controllers\DefaultController::class, 'cors'])->where('path', '.*');

Route::middleware(['cors'])->group(function () {
    Route::middleware(['auth:repo'])->group(function () {
        Route::get('/object/{key}', [\App\Http\Controllers\StorageController::class, 'download'])->where('key', '.+');
        Route::get('/object-exists/{key}', [\App\Http\Controllers\StorageController::class, 'exists'])->where('key', '.+');
        Route::get('/objects', [\App\Http\Controllers\StorageController::class, 'listObjects']);
    });

    Route::middleware(['auth:token'])->group(function () {
        Route::put('/object/{key}', [\App\Http\Controllers\StorageController::class, 'upload'])->where('key', '.+');
        Route::delete('/object/{key}', [\App\Http\Controllers\StorageController::class, 'delete'])->where('key', '.+');
    });
});
