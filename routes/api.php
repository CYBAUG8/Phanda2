<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ----------------- Reviews API -----------------

// Get all reviews for a specific provider
Route::get('/reviews/{provider_id}', [ReviewController::class, 'apiIndex']);

// Get all reviews for a specific user
Route::get('/reviews/user/{user_id}', [ReviewController::class, 'userReviews']);

Route::middleware('auth:sanctum')->group(function () {
    // Create or update a review
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Delete a review
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // User Info & Settings
    Route::prefix('settings')->group(function () {
        Route::get('/userInfo', [UserController::class, 'getUserInfo']);
        Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
        Route::post('/sendOtp', [UserController::class, 'sendOtp'])->middleware('throttle:6,1');
        Route::get('/getSettings', [SettingsController::class, 'getSettings']);
        Route::post('/settings', [SettingsController::class, 'toggleSettings']);
        Route::post('/notification-preferences', [SettingsController::class, 'updateNotificationPreferences']);
    });

    // Emergency Contact
    Route::prefix('emergency-contact')->group(function () {
        Route::get('/', [EmergencyContactController::class, 'show']);
        Route::post('/', [EmergencyContactController::class, 'store']);
        Route::put('/', [EmergencyContactController::class, 'store']); // Same as POST for update
        Route::delete('/', [EmergencyContactController::class, 'destroy']);
    });

    // Locations
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::post('/', [LocationController::class, 'store']);
        Route::put('/{id}', [LocationController::class, 'update']);
        Route::delete('/{id}', [LocationController::class, 'destroy']);
        Route::post('/{id}/default', [LocationController::class, 'setDefault']);
    });

    // Login History
    Route::prefix('login-history')->group(function () {
        Route::get('/', [LoginHistoryController::class, 'index']);
        Route::post('/', [LoginHistoryController::class, 'store']);
    });

    // Account Management
    Route::prefix('account')->group(function () {
        Route::post('/update-password', [AccountController::class, 'updatePassword']);
        Route::post('/update-contact-method', [AccountController::class, 'updateContactMethod']);
        Route::post('/toggle-data-sharing', [AccountController::class, 'toggleDataSharing']);
        Route::get('/export-data', [AccountController::class, 'exportData']);
        Route::delete('/', [AccountController::class, 'destroy']);
    });


    // Additional routes
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('provider-profile', [ProviderProfileController::class, 'store']);
Route::post('/service-request', [ServiceRequestController::class, 'store']);
Route::post('/services', [ServiceController::class, 'store']);
