<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Middleware\HasOrganizerPermissions;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::get('/me', 'me')->middleware('auth:sanctum');
});

Route::controller(EventController::class)->group(function () {
    Route::get('/events', 'index');
    Route::post('/events', 'store')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
    Route::get('/events/{event}', 'show');
    Route::put('/events/{event}', 'update')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
    Route::delete('/events/{event}', 'destroy')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
});
