<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;


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

