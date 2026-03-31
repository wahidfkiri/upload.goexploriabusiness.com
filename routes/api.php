<?php

use App\Http\Controllers\CDNController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::post('/upload', [CDNController::class, 'upload']);
    Route::post('/upload-multiple', [CDNController::class, 'uploadMultiple']);
    Route::delete('/file/{path}', [CDNController::class, 'deleteFile'])->where('path', '.*');
    Route::get('/files', [CDNController::class, 'listFiles']);
    Route::get('/temporary-url/{path}', [CDNController::class, 'temporaryUrl'])->where('path', '.*');
});

// Route publique pour accéder aux fichiers
Route::get('/storage/{path}', [CDNController::class, 'getFile'])->where('path', '.*');