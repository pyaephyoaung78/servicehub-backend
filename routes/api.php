<?php

use App\Http\Controllers\Api\Admin\AdminBookingAssignmentController;
use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminStaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\Staff\StaffAssignmentController;
use App\Http\Controllers\Api\Staff\StaffBookingWorkflowController;
use App\Http\Controllers\Api\Staff\StaffProfileController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);


    #admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
        Route::get('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'show']);
        Route::put('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{serviceCategory}', [ServiceCategoryController::class, 'destroy']);

        Route::post('/services', [ServiceController::class, 'store']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

        Route::get('/bookings', [AdminBookingController::class, 'index']);
        Route::get('/bookings/{booking}/eligible-staff', [AdminBookingAssignmentController::class, 'eligibleStaff',]);
        Route::post('/bookings/{booking}/assign', [AdminBookingAssignmentController::class, 'assign',]);
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show']);

        Route::get('/staff', [AdminStaffController::class, 'index']);
        Route::post('/staff', [AdminStaffController::class, 'store']);
        Route::get('/staff/{staffProfile}', [AdminStaffController::class, 'show']);
        Route::put('/staff/{staffProfile}', [AdminStaffController::class, 'update']);
        Route::delete('/staff/{staffProfile}', [AdminStaffController::class, 'destroy']);
    });


    #staff routes
    Route::middleware('role:staff')->prefix('staff')->group(function () {
        Route::get('/profile', [StaffProfileController::class, 'show']);
        Route::patch('/availability', [StaffProfileController::class, 'updateAvailability',]);

        Route::get('/assignments', [StaffAssignmentController::class, 'index']);
        Route::patch('/assignments/{assignment}/respond', [StaffAssignmentController::class, 'respond']);
        Route::patch('/assignments/{assignment}/work-status', [StaffBookingWorkflowController::class, 'updateStatus',]);
        Route::get('/assignments/{assignment}', [StaffAssignmentController::class, 'show']);
    });


    #customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
