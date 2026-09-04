<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SpeakingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Loaded by RouteServiceProvider inside a group with the "api" middleware
| and prefixed with "/api" - so the full path is /api/speaking/...
|
| Authentication uses Sanctum personal access tokens:
|   POST /api/register | /api/login   -> { token, user }
|   POST /api/logout | GET /api/me    (require the Bearer token)
| ------------------------------------------------------------------------
*/

// --- Public authentication -------------------------------------------------
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// --- Must be signed in ----------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::prefix('speaking')->group(function () {
        Route::post('submit', [SpeakingController::class, 'submit']);
        Route::get('attempts', [SpeakingController::class, 'attempts']);
        Route::get('attempts/{attempt}', [SpeakingController::class, 'show']);
    });
});

// --- Public catalogue (no need to be signed in to browse questions) -------
Route::prefix('speaking')->group(function () {
    Route::get('questions', [SpeakingController::class, 'questions']);
});
