<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProfileController;  
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderCalendarController;
use App\Http\Controllers\UserMessageController;
use App\Http\Controllers\ProviderMessageController;



Route::get('/', function () {
    return view('welcome');
});

// Provider page is public (no security required). Keep login page available if needed.
/*Route::get('/provider', function () {
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

// No-security demo entry point: accepts GET from simple form and redirects to provider home
Route::get('/provider/enter', function (Request $request) {
    return redirect('/provider');
});

Route::get('/user', function () {
    return view('user');
});*/

Route::get('/login', function () {
    return view('login');
})->name('login'); 

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout',[AuthController::class, 'logout']);


// Authenticated routes
Route::middleware('auth')->group(function () {
    // Provider area sample routes
    Route::get('/providers/dashboard', [ProviderDashboardController::class, 'index'])
        ->name('providers.dashboard');
    Route::post('/provider/toggle-online', [ProviderDashboardController::class, 'toggleOnline'])
        ->name('provider.toggleOnline');
    Route::get('/providers/bookings', [ProviderBookingController::class, 'index'])
        ->name('provider.bookings');
    Route::patch('/providers/bookings/{id}/confirm', [ProviderBookingController::class, 'confirm'])
        ->name('provider.bookings.confirm');

    Route::patch('/providers/bookings/{id}/start', [ProviderBookingController::class, 'start'])
        ->name('provider.bookings.start');

    Route::patch('/providers/bookings/{id}/complete', [ProviderBookingController::class, 'complete'])
        ->name('provider.bookings.complete');

    Route::patch('/providers/bookings/{id}/cancel', [ProviderBookingController::class, 'cancel'])
        ->name('provider.bookings.cancel');

    Route::get('/providers/services', function () {
        return view('providers.services');
    });

    Route::get('/providers/schedule', function () {
        return view('providers.schedule');
    });

    Route::get('/providers/earnings', function () {
        return view('providers.earnings');
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

    Route::get('/users/services', [UserServiceController::class, 'index'])
        ->name('users.services');

     Route::get('/users/bookings', [UserBookingController::class, 'index'])
        ->name('users.bookings');
    Route::post('/users/bookings', [UserBookingController::class, 'store'])
        ->name('users.bookings.store');
    Route::patch('/users/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])
        ->name('users.bookings.cancel');

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

    Route::put('provider-profile', [ProviderProfileController::class, 'update'])->name('provider.profile.update');
    Route::delete('provider-profile', [ProviderProfileController::class, 'destroy']);

    
    Route::get('/provider/calendar', [ProviderCalendarController::class, 'index'])
        ->name('provider.calendar');

    Route::get('/provider/calendar/events', [ProviderCalendarController::class, 'events'])
        ->name('provider.calendar.events');

    Route::post('/provider/calendar/{id}/status', [ProviderCalendarController::class, 'updateStatus'])
        ->name('provider.calendar.updateStatus');

    //Messages routes
    Route::get('/users/messages', [UserMessageController::class, 'index'])->name('user.messages');
    Route::get('/users/messages/{conversation}', [UserMessageController::class, 'show'])->name('user.messages.show');
    Route::post('/users/messages/send', [UserMessageController::class, 'send'])->name('user.messages.send');
    Route::get('/users/messages/{conversation}/latest',[UserMessageController::class, 'latest']);

    Route::get('/providers/messages', [ProviderMessageController::class, 'index'])
    ->name('provider.messages');

    Route::get('/providers/messages/{conversation}', [ProviderMessageController::class, 'show'])
    ->name('provider.messages.show');

    Route::post('/providers/messages/send', [ProviderMessageController::class, 'send'])
    ->name('provider.messages.send');
    
    Route::get('/providers/messages/{conversation}/latest',[ProviderMessageController::class, 'latest']);

    Route::post('/providers/messages', [ProviderMessageController::class, 'store'])
    ->name('provider.messages.store');
    
    Route::get('providers/messages/', [ProviderMessageController::class, 'index'])->name('provider.messages');
    Route::post('providers/messages/send', [ProviderMessageController::class, 'send'])->name('provider.messages.send');
    Route::get('providers/messages/latest/{user}', [ProviderMessageController::class, 'latestByUser'])->name('provider.messages.latest');

});
Route::prefix('providers')->middleware(['auth'])->group(function() {
    Route::get('services', [ProviderServiceController::class, 'index'])->name('provider.services.index');
    Route::post('services', [ProviderServiceController::class, 'store'])->name('provider.services.store');
    Route::patch('services/{service}/toggle', [ProviderServiceController::class, 'toggle'])->name('provider.services.toggle');
    Route::delete('services/{service}', [ProviderServiceController::class, 'destroy'])->name('provider.services.destroy');
    Route::patch('services/{service}', [ProviderServiceController::class, 'update'])->name('provider.services.update');
});


Route::get('/providers/profile', [ProviderProfileController::class, 'profile'])->name('provider.profile');
