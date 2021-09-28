<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


Route::apiResource('payment-notifications', NotificationController::class);

// Route::get('payment-notifications', [NotificationController::class, 'index']);
