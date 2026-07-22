<?php

use App\Http\Controllers\Web\Admin\AdminAuthController;
use App\Http\Controllers\Web\Admin\AdminBookingController;
use App\Http\Controllers\Web\Admin\AdminDashboardController;
use App\Http\Controllers\Web\Admin\AdminInvoiceController;
use App\Http\Controllers\Web\Admin\AdminPaymentController;
use App\Http\Controllers\Web\Admin\AdminQuotationController;
use App\Http\Controllers\Web\Admin\AdminReportController;
use App\Http\Controllers\Web\Admin\AdminStaffController;
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

            Route::get('/bookings', [
                AdminBookingController::class,
                'index',
            ])->name('bookings.index');

            Route::get('/bookings/{booking}', [
                AdminBookingController::class,
                'show',
            ])->name('bookings.show');

            Route::patch('/bookings/{booking}/cancel', [
                AdminBookingController::class,
                'cancel',
            ])->name('bookings.cancel');

            Route::patch('/bookings/{booking}/reject', [
                AdminBookingController::class,
                'reject',
            ])->name('bookings.reject');

            Route::get('/quotations', [
                AdminQuotationController::class,
                'index',
            ])->name('quotations.index');

            Route::get('/quotations/create', [
                AdminQuotationController::class,
                'create',
            ])->name('quotations.create');

            Route::post('/quotations', [
                AdminQuotationController::class,
                'store',
            ])->name('quotations.store');

            Route::get('/quotations/{quotation}', [
                AdminQuotationController::class,
                'show',
            ])->name('quotations.show');

            Route::get('/invoices', [
                AdminInvoiceController::class,
                'index',
            ])->name('invoices.index');

            Route::get('/invoices/create', [
                AdminInvoiceController::class,
                'create',
            ])->name('invoices.create');

            Route::post('/invoices', [
                AdminInvoiceController::class,
                'store',
            ])->name('invoices.store');

            Route::get('/invoices/{invoice}', [
                AdminInvoiceController::class,
                'show',
            ])->name('invoices.show');

            Route::post('/invoices/{invoice}/payments', [
                AdminInvoiceController::class,
                'recordPayment',
            ])->name('invoices.payments.store');

            Route::get('/payments', [
                AdminPaymentController::class,
                'index',
            ])->name('payments.index');

            Route::get('/payments/{payment}', [
                AdminPaymentController::class,
                'show',
            ])->name('payments.show');

            Route::get('/reports', [
                AdminReportController::class,
                'index',
            ])->name('reports.index');

            Route::get('/staff', [
                AdminStaffController::class,
                'index',
            ])->name('staff.index');

            Route::get('/staff/create', [
                AdminStaffController::class,
                'create',
            ])->name('staff.create');

            Route::post('/staff', [
                AdminStaffController::class,
                'store',
            ])->name('staff.store');

            Route::get('/staff/{staffProfile}', [
                AdminStaffController::class,
                'show',
            ])->name('staff.show');

            Route::get('/staff/{staffProfile}/edit', [
                AdminStaffController::class,
                'edit',
            ])->name('staff.edit');

            Route::put('/staff/{staffProfile}', [
                AdminStaffController::class,
                'update',
            ])->name('staff.update');

            Route::delete('/staff/{staffProfile}', [
                AdminStaffController::class,
                'destroy',
            ])->name('staff.destroy');
        });
    });
