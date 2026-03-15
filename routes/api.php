<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\SubAdminController;
use App\Http\Controllers\Api\SystemAdminController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public APIs (No Login Required)
|--------------------------------------------------------------------------
*/

// Districts
Route::get('/districts', [DistrictController::class, 'index']);
Route::get('/district/{id}', [DistrictController::class, 'show']);
Route::get('/district/{id}/facilities', [DistrictController::class, 'facilities']);
Route::get('/facilities', [DistrictController::class, 'allFacilities']);
Route::get('/facility/{slug}', [DistrictController::class, 'getFacilityBySlug']);
Route::get('/district/{id}/sports', [DistrictController::class, 'sports']);

// Sports & Pricing
Route::get('/sport/{id}/pricing', [SportController::class, 'pricing']);

// Calendar (public view of confirmed bookings)
Route::get('/calendar', [CalendarController::class, 'index']);

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Check Availability (public — no login required)
Route::post('/check-availability', [BookingController::class, 'checkAvailability']);

// Bookings (public — guests can book without login)
Route::post('/bookings', [BookingController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authenticated User APIs
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/my-profile', [AuthController::class, 'profile']);
    Route::match(['put', 'post'], '/my-profile', [AuthController::class, 'updateProfile']);

    // Bookings (authenticated user's own bookings)
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    // Subscriptions
    Route::get('/my-subscriptions', [UserController::class, 'subscriptions']);
    Route::post('/subscriptions', [UserController::class, 'createSubscription']);
    Route::put('/subscriptions/{id}/cancel', [UserController::class, 'cancelSubscription']);
    Route::get('/my-transactions', [UserController::class, 'transactions']);

    /*
    |--------------------------------------------------------------------------
    | Sub Admin APIs (District Restricted)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:sub_admin,system_admin', 'district.restrict'])
        ->prefix('subadmin')
        ->group(function () {
            // Facilities
            Route::get('/facilities', [SubAdminController::class, 'facilities']);
            Route::post('/facilities', [SubAdminController::class, 'addFacility']);
            Route::put('/facilities/{id}', [SubAdminController::class, 'updateFacility']);
            Route::delete('/facilities/{id}', [SubAdminController::class, 'deleteFacility']);

            // Sports
            Route::get('/sports', [SubAdminController::class, 'sports']);
            Route::post('/sports', [SubAdminController::class, 'addSport']);
            Route::delete('/sports/{id}', [SubAdminController::class, 'deleteSport']);

            // Bookings
            Route::get('/bookings', [SubAdminController::class, 'bookings']);
            Route::put('/bookings/{id}/confirm', [SubAdminController::class, 'confirmBooking']);
            Route::put('/bookings/{id}/reject', [SubAdminController::class, 'rejectBooking']);

            // Dashboard & Reports
            Route::get('/dashboard', [SubAdminController::class, 'dashboard']);
            Route::get('/reports', [SubAdminController::class, 'reports']);

            // Pricing
            Route::post('/pricing', [SubAdminController::class, 'addPricing']);

            // User Management (District Restricted)
            Route::get('/users', [SubAdminController::class, 'users']);
        });

    /*
    |--------------------------------------------------------------------------
    | System Admin APIs
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:system_admin')
        ->prefix('systemadmin')
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [SystemAdminController::class, 'dashboard']);

            // Sub Admin Management
            Route::post('/create-subadmin', [SystemAdminController::class, 'createSubAdmin']);
            Route::get('/sub-admins', [SystemAdminController::class, 'subAdmins']);
            Route::delete('/sub-admin/{id}', [SystemAdminController::class, 'deleteSubAdmin']);

            // District Management
            Route::get('/districts', [SystemAdminController::class, 'districts']);
            Route::post('/district', [SystemAdminController::class, 'createDistrict']);
            Route::put('/district/{id}', [SystemAdminController::class, 'updateDistrict']);

            // All Bookings
            Route::get('/all-bookings', [SystemAdminController::class, 'allBookings']);

            // Facility/Sport/Pricing Management (Restricted to System Admin)
            Route::post('/facility', [SubAdminController::class, 'addFacility']);
            Route::delete('/facility/{id}', [SubAdminController::class, 'deleteFacility']);
            Route::post('/sport', [SubAdminController::class, 'addSport']);
            Route::post('/pricing', [SubAdminController::class, 'addPricing']);

            // User Management (Global)
            Route::get('/users', [SystemAdminController::class, 'users']);
            Route::match(['PUT', 'PATCH'], '/user-profile/{id}', [SystemAdminController::class, 'updateUser']);
            Route::delete('/user/{id}', [SystemAdminController::class, 'deleteUser']);
        });
});
