<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiktokController;

Route::get('/', [TiktokController::class, 'index'])->name('tiktok.index');
Route::post('/download', [TiktokController::class, 'download'])->name('tiktok.download');
Route::get('/download', fn () => redirect()->route('tiktok.index'));
Route::get('/stream', [TiktokController::class, 'stream'])->name('tiktok.stream');
