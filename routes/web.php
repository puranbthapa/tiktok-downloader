<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiktokController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\PinterestController;
// use App\Http\Controllers\YoutubeController; // Disabled - YouTube API not working
use App\Http\Controllers\AdminController;

// Home
Route::get('/', fn () => view('home'))->name('home');

// TikTok
Route::prefix('tiktok')->group(function () {
    Route::get('/',         [TiktokController::class, 'index'])->name('tiktok.index');
    Route::post('/download', [TiktokController::class, 'download'])->name('tiktok.download');
    Route::get('/download',  fn () => redirect()->route('tiktok.index'));
    Route::get('/stream',    [TiktokController::class, 'stream'])->name('tiktok.stream');
});

// Facebook
Route::prefix('facebook')->group(function () {
    Route::get('/',         [FacebookController::class, 'index'])->name('facebook.index');
    Route::post('/download', [FacebookController::class, 'download'])->name('facebook.download');
    Route::get('/download',  fn () => redirect()->route('facebook.index'));
    Route::get('/stream',    [FacebookController::class, 'stream'])->name('facebook.stream');
});

// YouTube - Disabled due to API unavailability
// Route::prefix('youtube')->group(function () {
//     Route::get('/',         [YoutubeController::class, 'index'])->name('youtube.index');
//     Route::post('/download', [YoutubeController::class, 'download'])->name('youtube.download');
//     Route::get('/download',  fn () => redirect()->route('youtube.index'));
//     Route::get('/stream',    [YoutubeController::class, 'stream'])->name('youtube.stream');
// });

// Pinterest
Route::prefix('pinterest')->group(function () {
    Route::get('/',         [PinterestController::class, 'index'])->name('pinterest.index');
    Route::post('/download', [PinterestController::class, 'download'])->name('pinterest.download');
    Route::get('/download',  fn () => redirect()->route('pinterest.index'));
    Route::get('/stream',    [PinterestController::class, 'stream'])->name('pinterest.stream');
});

// Admin auth
Route::get('/admin/login',  [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Admin protected
Route::middleware(\App\Http\Middleware\AdminAuth::class)->prefix('admin')->group(function () {
    Route::get('/',             [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/logs',         [AdminController::class, 'logs'])->name('admin.logs');
    Route::get('/settings',     [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/export',       [AdminController::class, 'exportCsv'])->name('admin.export');
    Route::delete('/logs/{id}', [AdminController::class, 'deleteLog'])->name('admin.logs.delete');
    Route::delete('/logs',      [AdminController::class, 'clearLogs'])->name('admin.logs.clear');
});
