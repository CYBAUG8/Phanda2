<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
//Service and Booking imports
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\UserBookingController;
use App\Http\Controllers\UserPaymentController;
//profile
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\RecoveryContactController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\ProfileController;  
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderCalendarController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderEarningsController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\UserMessageController;
use App\Http\Controllers\ProviderMessageController;
use Illuminate\Support\Facades\Http;



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
    return redirect('/provider');
});

Route::get('/user', function () {
    return view('user');
});

// Provider area sample routes
Route::get('/providers/dashboard', [ProviderDashboardController::class, 'index'])
    ->middleware('auth')
    ->name('providers.dashboard');

Route::get('/providers/bookings', [ProviderBookingController::class, 'index'])
    ->middleware('auth')
    ->name('provider.bookings.index');

Route::get('/providers/services', [ProviderServiceController::class, 'index'])
    ->middleware('auth')
    ->name('provider.services.index');

Route::get('/providers/schedule', function () {
    return view('Providers.schedule');
});

Route::get('/providers/earnings', [ProviderEarningsController::class, 'index'])
    ->middleware('auth')
    ->name('provider.earnings');


// User area sample routes

Route::get('/users/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('users.dashboard');


Route::get('/users/profile', function () {
    return view('Users.profile');
});


Route::get('/users/reviews', [ReviewController::class, 'index'])
    ->middleware('auth')
    ->name('reviews.reviews');
Route::post('/users/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('reviews.store');
Route::delete('/users/reviews/{id}', [ReviewController::class, 'destroy'])
    ->middleware('auth')
    ->name('reviews.destroy');

Route::get('/users/services', [UserServiceController::class, 'index'])->name('users.services');

Route::get('/users/bookings', [UserBookingController::class, 'index'])->name('users.bookings');
Route::post('/users/bookings', [UserBookingController::class, 'store'])->name('users.bookings.store');
Route::patch('/users/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('users.bookings.cancel');
Route::get('/users/bookings/{booking}/checkout', [UserPaymentController::class, 'showCheckout'])->name('users.payments.checkout');
Route::post('/users/bookings/{booking}/payments/initiate', [UserPaymentController::class, 'initiate'])->name('users.payments.initiate');
Route::post('/users/bookings/{booking}/payments/simulate-success', [UserPaymentController::class, 'simulateSuccess'])->name('users.payments.simulate-success');
Route::post('/users/bookings/{booking}/payments/simulate-failure', [UserPaymentController::class, 'simulateFailure'])->name('users.payments.simulate-failure');

Route::get('/login', function () {
    return view('login');
})->name('login'); 

Route::get('/register', function () {
    return view('signup');
})->name('signup');

Route::post('/register', [AuthController::class, 'register'])->name('signup.submit');




Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/api/places/search', function (Request $request) {
    $query = $request->query('q');

    if (!$query) {
        return response()->json([]);
    }

    try {
        $response = Http::withoutVerifying()  // ← fixes SSL on localhost
            ->withHeaders([
                'User-Agent' => 'PhandaApp/1.0'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'countrycodes' => 'za',
                'format' => 'json',
                'limit' => 5
            ]);

        return response()->json($response->json());

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Authenticated routes
Route::middleware('auth')->group(function () {

    
    Route::get('/logout', [AuthController::class, 'logout']);
    
    // User info routes
    Route::get('/userInfo', [UserController::class, 'getUserInfo']);
    Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
    Route::post('/sendOtp', [UserController::class, 'sendOtp'])->middleware('throttle:6,1');
    Route::post('/update-password', [UserController::class, 'updatePassword']);
    Route::delete('/account', [UserController::class, 'deleteAccount']);

    // Settings routes
    Route::get('/getSettings', [SettingsController::class, 'getSettings']);
    Route::post('/settings', [SettingsController::class, 'toggleSettings']);

    // Emergency contact routes
    Route::get('/emergency-contact', [EmergencyContactController::class, 'show']);
    Route::post('/emergency-contact', [EmergencyContactController::class, 'store']);
    Route::put('/emergency-contact', [EmergencyContactController::class, 'store']);
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
        return view('Users.settings'); 
    })->name('users.settings');

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile'])->name('api.profile.get');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('api.profile.update');
        Route::post('/update-with-otp', [ProfileController::class, 'updateWithOtp'])->name('api.profile.verify-otp');
        Route::post('/send-otp', [ProfileController::class, 'sendOtp'])->middleware('throttle:6,1')->name('api.profile.send-otp');
        Route::delete('/', [ProfileController::class, 'deleteAccount'])->name('api.profile.delete');
    
        Route::get('/addresses', [ProfileController::class, 'getAddresses'])->name('api.profile.addresses.get');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('api.profile.address.store');
        Route::put('/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('api.profile.address.update');
        Route::delete('/addresses/{id}', [ProfileController::class, 'destroyAddress'])->name('api.profile.address.destroy');
        Route::post('/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('api.profile.address.set-default');
    });

    Route::put('provider-profile', [ProviderProfileController::class, 'update'])->name('provider.profile.update');
    Route::delete('provider-profile', [ProviderProfileController::class, 'destroy']);
    Route::post('/providers/services', [ProviderServiceController::class, 'store'])
        ->name('provider.services.store');
    Route::put('/providers/services/{service}', [ProviderServiceController::class, 'update'])
        ->name('provider.services.update');
    Route::patch('/providers/services/{service}/toggle', [ProviderServiceController::class, 'toggle'])
        ->name('provider.services.toggle');
    Route::delete('/providers/services/{service}', [ProviderServiceController::class, 'destroy'])
        ->name('provider.services.destroy');
    Route::patch('/providers/bookings/{id}/confirm', [ProviderBookingController::class, 'confirm'])
        ->name('provider.bookings.confirm');
    Route::patch('/providers/bookings/{id}/start', [ProviderBookingController::class, 'start'])
        ->name('provider.bookings.start');
    Route::patch('/providers/bookings/{id}/complete', [ProviderBookingController::class, 'complete'])
        ->name('provider.bookings.complete');
    Route::patch('/providers/bookings/{id}/cancel', [ProviderBookingController::class, 'cancel'])
        ->name('provider.bookings.cancel');



    
    Route::get('/provider/calendar', [ProviderCalendarController::class, 'index'])
        ->name('provider.calendar');

    Route::get('/provider/calendar/events', [ProviderCalendarController::class, 'events'])
        ->name('provider.calendar.events');

    Route::post('/provider/calendar/{id}/status', [ProviderCalendarController::class, 'updateStatus'])
        ->name('provider.calendar.updateStatus');

    Route::post('/providers/toggle-online', [ProviderDashboardController::class, 'toggleOnline'])
        ->name('provider.toggleOnline');

    Route::post('/providers/earnings/withdraw', [ProviderEarningsController::class, 'withdraw'])
        ->name('provider.earnings.withdraw');

    //Messages routes
    Route::get('/users/messages', [UserMessageController::class, 'index'])->name('user.messages');
    Route::post('/users/messages/send', [UserMessageController::class, 'send'])->name('user.messages.send');
    Route::get('/users/messages/list', [UserMessageController::class, 'conversationList'])
    ->name('user.messages.list');
    Route::get('/users/messages/{conversation}/latest',[UserMessageController::class, 'latest']);
    Route::post('/users/messages/{conversation}/read', [UserMessageController::class, 'markRead'])
    ->name('user.messages.read');
    Route::get('/users/messages/{conversation}', [UserMessageController::class, 'show'])->name('user.messages.show');
    
    
    Route::get('/providers/messages/start/{customer}', [ProviderMessageController::class, 'startConversation'])->name('providers.messages.start');
    Route::get('/providers/messages/list', [ProviderMessageController::class, 'conversationList'])
    ->name('provider.messages.list');
    Route::post('/providers/messages/{conversation}/read', [ProviderMessageController::class, 'markRead'])
    ->name('provider.messages.read');
    
    Route::get('/providers/messages', [ProviderMessageController::class, 'index'])
    ->name('provider.messages');

    Route::get('/providers/messages/{conversation}', [ProviderMessageController::class, 'show'])
    ->name('provider.messages.show');

    Route::post('/providers/messages/send', [ProviderMessageController::class, 'send'])
    ->name('provider.messages.send');
    
    Route::get('/providers/messages/{conversation}/latest',[ProviderMessageController::class, 'latest']);

    Route::get('/providers/profile', [ProviderProfileController::class, 'profile'])->name('providers.profile');
});





















