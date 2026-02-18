<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BookingController;
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

Route::controller(TicketController::class)->group(function () {
    Route::post('/events/{event}/tickets', 'store')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
    Route::put('/tickets/{ticket}', 'update')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
    Route::delete('/tickets/{ticket}', 'destroy')->middleware(['auth:sanctum', HasOrganizerPermissions::class]);
});

Route::controller(BookingController::class)->group(function () {
    Route::get('/bookings', 'index')->middleware('auth:sanctum');
    Route::post('/tickets/{ticket}/bookings', 'store')->middleware('auth:sanctum');
    Route::put('/bookings/{booking}/cancel', 'cancel')->middleware('auth:sanctum');
});
