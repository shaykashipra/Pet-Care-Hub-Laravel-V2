<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| PetCareHub REST API  —  v1
|--------------------------------------------------------------------------
|
|  Base URL : /api/v1/
|  Auth     : Bearer JWT token (Authorization: Bearer <token>)
|
|  Public routes  → no middleware
|  Protected      → 'jwt.auth' middleware
|  Admin only     → 'jwt.auth' + manual role check in controller
|
*/

Route::prefix('v1')->group(function () {

    // ─────────────────────────────────────────────────────────────────────
    //  🔐  AUTH  (Public)
    // ─────────────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register',      [AuthController::class, 'register']);
        Route::post('/login',         [AuthController::class, 'login']);
        Route::post('/doctor/login',  [AuthController::class, 'doctorLogin']);
    });

    // ─────────────────────────────────────────────────────────────────────
    //  🐾  PETS  (Public — read only)
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/pets',       [PetController::class, 'index']);
    Route::get('/pets/{id}',  [PetController::class, 'show']);

    // ─────────────────────────────────────────────────────────────────────
    //  🩺  DOCTORS  (Public — read only)
    // ─────────────────────────────────────────────────────────────────────
    Route::get('/doctors',       [DoctorController::class, 'index']);
    Route::get('/doctors/{id}',  [DoctorController::class, 'show']);

    // ─────────────────────────────────────────────────────────────────────
    //  🔒  PROTECTED ROUTES (JWT required)
    // ─────────────────────────────────────────────────────────────────────
    Route::middleware('jwt.auth')->group(function () {

        // Auth
        Route::post('/auth/logout',  [AuthController::class, 'logout']);
        Route::get('/me',            [AuthController::class, 'me']);

        // Pets (write operations)
        Route::get('/pets/my/list',     [PetController::class, 'myPets']);
        Route::post('/pets',            [PetController::class, 'store']);
        Route::put('/pets/{id}',        [PetController::class, 'update']);
        Route::delete('/pets/{id}',     [PetController::class, 'destroy']);

        // Appointments
        Route::get('/appointments',         [AppointmentController::class, 'index']);
        Route::get('/appointments/{id}',    [AppointmentController::class, 'show']);
        Route::post('/appointments',        [AppointmentController::class, 'store']);
        Route::put('/appointments/{id}',    [AppointmentController::class, 'update']);
        Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

        // Favourites
        Route::get('/favourites',              [FavouriteController::class, 'index']);
        Route::post('/favourites/{petId}',     [FavouriteController::class, 'store']);
        Route::delete('/favourites/{petId}',   [FavouriteController::class, 'destroy']);

        // ─────────────────────────────────────────────────────────────────
        //  👑  ADMIN  (jwt.auth + admin role check inside controller)
        // ─────────────────────────────────────────────────────────────────
        Route::prefix('admin')->group(function () {
            Route::get('/stats',                 [AdminController::class, 'stats']);
            Route::get('/users',                 [AdminController::class, 'users']);
            Route::delete('/users/{id}',         [AdminController::class, 'destroyUser']);
            Route::get('/pets',                  [AdminController::class, 'pets']);
            Route::patch('/pets/{id}',           [AdminController::class, 'updatePet']);
            Route::delete('/pets/{id}',          [AdminController::class, 'destroyPet']);
            Route::get('/appointments',          [AdminController::class, 'appointments']);
        });
    });
});

// ─────────────────────────────────────────────────────────────────────
//  API fallback — 404 for unknown API routes
// ─────────────────────────────────────────────────────────────────────
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
    ], 404);
});
