<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\UserBookingController;
use App\Http\Controllers\UserPaymentController;
//profile

use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\RecoveryContactController;
use App\Http\Controllers\EmergencyContactController;

use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ProviderBookingController;
use App\Http\Controllers\ProviderEarningsController;
use App\Http\Controllers\ProviderPayoutController;
use App\Http\Controllers\ProviderProfileController;
use App\Http\Controllers\ProviderCalendarController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserMessageController;
use App\Http\Controllers\ProviderMessageController;
use App\Http\Controllers\ServiceController;

/*`
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
 Route::post('/register',[AuthController::class, 'register'])->name('register.submit');

/*
|--------------------------------------------------------------------------
| Location Autocomplete API
|--------------------------------------------------------------------------
*/

Route::get('/api/places/search', function (Request $request) {

    $query = $request->query('q');

    if (!$query) {
        return response()->json([]);
    }

    try {

        $response = Http::withoutVerifying()
            ->withHeaders([
                'User-Agent' => 'PhandaApp/1.0'
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
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


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Provider Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/providers/dashboard', [ProviderDashboardController::class, 'index'])
        ->name('providers.dashboard');

    Route::post('/provider/toggle-online', [ProviderDashboardController::class, 'toggleOnline'])
        ->name('provider.toggleOnline');

    Route::get('/providers/bookings', [ProviderBookingController::class, 'index'])
        ->name('providers.bookings');

    Route::patch('/providers/bookings/{id}/confirm', [ProviderBookingController::class, 'confirm'])
        ->name('providers.bookings.confirm');

    Route::patch('/providers/bookings/{id}/start', [ProviderBookingController::class, 'start'])
        ->name('providers.bookings.start');

    Route::patch('/providers/bookings/{id}/complete', [ProviderBookingController::class, 'complete'])
        ->name('providers.bookings.complete');

    Route::patch('/providers/bookings/{id}/cancel', [ProviderBookingController::class, 'cancel'])
        ->name('providers.bookings.cancel');
    Route::get('/providers/schedule', function () {
        return view('providers.schedule');
    });

    Route::get('/providers/earnings', [ProviderEarningsController::class, 'index'])
        ->name('providers.earnings');
    Route::post('/providers/payout/request', [ProviderPayoutController::class,'requestPayout'])
    ->name('providers.payout.request');
    Route::get('/providers/payout/refresh', [ProviderEarningsController::class, 'refreshPayouts'])
     ->name('providers.payout.refresh');


    /*
    |--------------------------------------------------------------------------
    | Provider Services
    |--------------------------------------------------------------------------
    */

    Route::prefix('providers')->group(function () {

        Route::get('services', [ProviderServiceController::class, 'index'])
            ->name('provider.services.index');

        Route::post('services', [ProviderServiceController::class, 'store'])
            ->name('provider.services.store');

        Route::patch('services/{service}/toggle', [ProviderServiceController::class, 'toggle'])
            ->name('provider.services.toggle');

        Route::patch('services/{service}', [ProviderServiceController::class, 'update'])
            ->name('provider.services.update');

        Route::delete('services/{service}', [ProviderServiceController::class, 'destroy'])
            ->name('provider.services.destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | Provider Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/providers/profile', [ProviderProfileController::class, 'profile'])
        ->name('providers.profile');

    Route::put('provider-profile', [ProviderProfileController::class, 'update'])
        ->name('provider.profile.update');

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




    /*
    |--------------------------------------------------------------------------
    | Provider Calendar
    |--------------------------------------------------------------------------
    */

    Route::get('/provider/calendar', [ProviderCalendarController::class, 'index'])
        ->name('provider.calendar');

    Route::get('/provider/calendar/events', [ProviderCalendarController::class, 'events'])
        ->name('provider.calendar.events');

    Route::post('/provider/calendar/{id}/status', [ProviderCalendarController::class, 'updateStatus'])
        ->name('provider.calendar.updateStatus');


    /*
    |--------------------------------------------------------------------------
    | User Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/users/dashboard', [DashboardController::class, 'index'])
        ->name('users.dashboard');

    Route::get('/users/services', [UserServiceController::class, 'index'])
        ->name('users.services');

    Route::get('/users/bookings', [UserBookingController::class, 'index'])
        ->name('users.bookings');

    Route::post('/users/bookings', [UserBookingController::class, 'store'])
        ->name('users.bookings.store');

    Route::patch('/users/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])
        ->name('users.bookings.cancel');

    Route::get('/users/profile', function () {
        return view('users.profile');
    });

    Route::get('/users/settings', function () {
        return view('users.settings');
    })->name('users.settings');


    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */

    Route::get('/users/reviews', [ReviewController::class, 'index'])
        ->name('reviews.reviews');

    Route::post('/users/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::delete('/users/reviews/{id}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');


    /*
    |--------------------------------------------------------------------------
    | Profile + Account
    |--------------------------------------------------------------------------
    */

    Route::get('/userInfo', [UserController::class, 'getUserInfo']);
    Route::post('/updateUserInfo', [UserController::class, 'updateUserInfo']);
    Route::post('/sendOtp', [UserController::class, 'sendOtp']);
    Route::post('/update-password', [UserController::class, 'updatePassword']);
    Route::delete('/account', [UserController::class, 'deleteAccount']);
    Route::get('/download-my-data', [UserController::class, 'downloadData']);

    Route::get('/getSettings', [SettingsController::class, 'getSettings']);
    Route::post('/settings', [SettingsController::class, 'toggleSettings']);

    Route::get('/login-history', [LoginHistoryController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | Emergency + Recovery
    |--------------------------------------------------------------------------
    */

    Route::get('/emergency-contact', [EmergencyContactController::class, 'show']);
    Route::post('/emergency-contact', [EmergencyContactController::class, 'store']);
    Route::put('/emergency-contact', [EmergencyContactController::class, 'update']);
    Route::delete('/emergency-contact', [EmergencyContactController::class, 'destroy']);

    Route::get('/recovery-contact', [RecoveryContactController::class, 'show']);
    Route::post('/recovery-contact', [RecoveryContactController::class, 'store']);
    Route::put('/recovery-contact', [RecoveryContactController::class, 'update']);
    Route::delete('/recovery-contact', [RecoveryContactController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    */

    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::put('/locations/{location_id}', [LocationController::class, 'update']);
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Messaging
    |--------------------------------------------------------------------------
    */

    // USER MESSAGES
    Route::get('/users/messages', [UserMessageController::class, 'index'])
        ->name('user.messages');

    Route::post('/users/messages/send', [UserMessageController::class, 'send'])
        ->name('user.messages.send');

    Route::get('/users/messages/list', [UserMessageController::class, 'conversationList'])
        ->name('user.messages.list');

    Route::get('/users/messages/{conversation}', [UserMessageController::class, 'show'])
        ->name('user.messages.show');

    Route::get('/users/messages/{conversation}/latest', [UserMessageController::class, 'latest']);

    Route::post('/users/messages/{conversation}/read', [UserMessageController::class, 'markRead'])
        ->name('user.messages.read');
    
    Route::post('/users/bookings/{booking}/payments/initiate', [UserPaymentController::class, 'initiate'])
    ->middleware('auth')
    ->name('users.payments.initiate');
    
    Route::post('/users/messages/start', [UserMessageController::class, 'startConversation'])
        ->name('user.messages.start');

    // PROVIDER MESSAGES
    Route::get('/providers/messages/start/{customer}', [ProviderMessageController::class, 'startConversation'])
        ->name('providers.messages.start');

    Route::get('/providers/messages', [ProviderMessageController::class, 'index'])
        ->name('provider.messages');

    Route::get('/providers/messages/list', [ProviderMessageController::class, 'conversationList'])
        ->name('provider.messages.list');

    Route::get('/providers/messages/{conversation}', [ProviderMessageController::class, 'show'])
        ->name('provider.messages.show');

    Route::post('/providers/messages/send', [ProviderMessageController::class, 'send'])
        ->name('provider.messages.send');

    Route::get('/providers/messages/{conversation}/latest', [ProviderMessageController::class, 'latest']);

    Route::post('/providers/messages/{conversation}/read', [ProviderMessageController::class, 'markRead'])
        ->name('provider.messages.read');

        Route::get('/', [ProfileController::class, 'getProfile'])->name('api.profile.get');
        Route::post('/update', [ProfileController::class, 'updateProfile'])->name('api.profile.update');
        Route::post('/update-with-otp', [ProfileController::class, 'updateWithOtp'])->name('api.profile.verify-otp');
        Route::post('/send-otp', [UserController::class, 'sendOtp'])->name('api.profile.send-otp');
        Route::delete('/', [ProfileController::class, 'deleteAccount'])->name('api.profile.delete');
    
        Route::get('/addresses', [ProfileController::class, 'getAddresses'])->name('api.profile.addresses.get');
        Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('api.profile.address.store');
        Route::put('/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('api.profile.address.update');
        Route::delete('/addresses/{id}', [ProfileController::class, 'destroyAddress'])->name('api.profile.address.destroy');
        Route::post('/addresses/{id}/default', [ProfileController::class, 'setDefaultAddress'])->name('api.profile.address.set-default');
});
