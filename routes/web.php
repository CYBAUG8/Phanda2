<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
//Service and Booking imports
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\UserBookingController;
//profile
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\RecoveryContactController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\ProfileController;  




Route::get('/', function () {
    return view('welcome');
});

// Provider page is public (no security required). Keep login page available if needed.
Route::get('/provider', function () {
    return view('provider');
});

Route::get('/provider/login', function () {
    return view('provider_login');
});

// Optional demo login (kept for convenience) — not enforced by /provider route
Route::post('/provider/login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    if ($email === 'provider@example.com' && $password === 'secret') {
        $request->session()->put('provider_authenticated', true);
        return redirect('/provider');
    }

    return back()->with('error', 'Invalid credentials');
});

Route::get('/provider/logout', function (Request $request) {
    $request->session()->forget('provider_authenticated');
    return redirect('/');
});

// No-security demo entry point: accepts GET from simple form and redirects to provider home
Route::get('/provider/enter', function (Request $request) {
    // For the "no security" flow we simply redirect to the provider page.
    // You can inspect $request->query('email') if you want to record/demo the value.
    return redirect('/provider');
});

Route::get('/user', function () {
    return view('user');
});

// Provider area sample routes
Route::get('/providers/dashboard', function () {
    return view('providers.dashboard');
});

Route::get('/providers/bookings', function () {
    return view('providers.bookings');
});

Route::get('/providers/services', function () {
    return view('providers.services');
});

Route::get('/providers/schedule', function () {
    return view('providers.schedule');
});

Route::get('/providers/earnings', function () {
    return view('providers.earnings');
});

Route::get('/providers/messages', function () {
    return view('providers.messages');
});

Route::get('/providers/profile', function () {
    return view('providers.profile');
});

// User area sample routes

Route::get('/users/dashboard', [DashboardController::class, 'index'])
    ->name('users.dashboard');

Route::get('/users/services', function () {
    return view('users.services');
});

Route::get('/users/bookings', function () {
    return view('users.bookings');
});

Route::get('/users/messages', function () {
    return view('users.messages');
});

Route::get('/users/profile', function () {
    return view('users.profile');
});

Route::get('/users/settings', function () {
    return view('users.settings');
});
Route::get('/users/reviews', [ReviewController::class, 'index'])
    ->name('reviews.reviews');
Route::post('users/reviews', [ReviewController::class, 'store'])
    ->name('reviews.store');
Route::delete('/users/reviews/{id}', [ReviewController::class, 'destroy'])
    ->name('reviews.destroy');

Route::get('/users/services', [UserServiceController::class, 'index']);

Route::get('/users/bookings', [UserBookingController::class, 'index']);
Route::post('/users/bookings', [UserBookingController::class, 'store']);
Route::patch('/users/bookings/{booking}/cancel', [UserBookingController::class, 'cancel']);

Route::get('/login', function () {
    return view('login');
})->name('login'); 

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');


// Authenticated routes
Route::middleware('auth')->group(function () {
    
    // User routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User info routes
    Route::get('/userInfo', [UserController::class, 'getUserInfo']);
    Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
    Route::post('/sendOtp', [UserController::class, 'sendOtp']);
    Route::post('/update-password', [UserController::class, 'updatePassword']);
    Route::delete('/account', [UserController::class, 'deleteAccount']);

    // Settings routes
    Route::get('/getSettings', [SettingsController::class, 'getSettings']);
    Route::post('/settings', [SettingsController::class, 'toggleSettings']);

    // Emergency contact routes
    Route::get('/emergency-contact', [EmergencyContactController::class, 'show']);
    Route::post('/emergency-contact', [EmergencyContactController::class, 'store']);
    Route::put('/emergency-contact', [EmergencyContactController::class, 'update']);
    Route::delete('/emergency-contact', [EmergencyContactController::class, 'destroy']);

    // Location routes
    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::put('/locations/{location_id}', [LocationController::class, 'update']);
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']);

    
    Route::get('/login-history', [LoginHistoryController::class, 'index']);
     

    Route::get('/recovery-contact', [RecoveryContactController::class, 'show']);
    Route::post('/recovery-contact', [RecoveryContactController::class, 'store']);
    Route::put('/recovery-contact', [RecoveryContactController::class, 'update']);
    Route::delete('/recovery-contact', [RecoveryContactController::class, 'destroy']);
    Route::delete('/profile', [ProfileController::class, 'deleteAccount']);

    
    Route::get('/users/settings', function () {
        return view('users.settings'); 
    })->name('users.settings');
});


    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile'])->name('api.profile.get');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('api.profile.update');
        Route::post('/update-with-otp', [ProfileController::class, 'updateWithOtp'])->name('api.profile.verify-otp');
        Route::post('/send-otp', [ProfileController::class, 'sendOtp'])->name('api.profile.send-otp');
        Route::delete('/', [ProfileController::class, 'deleteAccount'])->name('api.profile.delete');
    

        Route::get('/addresses', [ProfileController::class, 'getAddresses'])->name('api.profile.addresses.get');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('api.profile.address.store');
        Route::put('/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('api.profile.address.update');
        Route::delete('/addresses/{id}', [ProfileController::class, 'destroyAddress'])->name('api.profile.address.destroy');
        Route::post('/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('api.profile.address.set-default');
    });