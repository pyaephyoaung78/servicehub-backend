<?php

use App\Http\Controllers\Web\Admin\AdminAuthController;
use App\Http\Controllers\Web\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', [
                AdminAuthController::class,
                'showLogin',
            ])->name('login');

            Route::post('/login', [
                AdminAuthController::class,
                'login',
            ])->name('login.store');
        });

        Route::middleware('admin.web')->group(function () {
            Route::get('/dashboard', [
                AdminDashboardController::class,
                'index',
            ])->name('dashboard');

            Route::post('/logout', [
                AdminAuthController::class,
                'logout',
            ])->name('logout');
        });
    });