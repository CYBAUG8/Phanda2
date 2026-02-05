<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

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
