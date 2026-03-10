<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
//profile
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Api\EmergencyContactController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\LoginHistoryController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ----------------- Reviews API -----------------

// Get all reviews for a specific provider
Route::get('/reviews/{provider_id}', [ReviewController::class, 'apiIndex']);

// Get all reviews for a specific user
Route::get('/reviews/user/{user_id}', [ReviewController::class, 'userReviews']);

// Create or update a review
Route::post('/reviews', [ReviewController::class, 'store']);

// Delete a review
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

// ----------------- Optional Authenticated Routes -----------------
// Uncomment if you want only authenticated users to access certain endpoints
/*
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // User Info & Settings
    Route::prefix('settings')->group(function () {
        Route::get('/userInfo', [SettingsController::class, 'getUserInfo']);
        Route::post('/updateUserInfo', [SettingsController::class, 'updateUserInfo']);
        Route::post('/sendOtp', [SettingsController::class, 'sendOtp']);
        Route::get('/getSettings', [SettingsController::class, 'getSettings']);
        Route::post('/settings', [SettingsController::class, 'updateSettings']);
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
            'user' => $request->user()
        ]);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('provider-profile', [ProviderProfileController::class, 'store']);
Route::post('/service-request', [ServiceRequestController::class, 'store']);
Route::post('/services', [ServiceController::class, 'store']);