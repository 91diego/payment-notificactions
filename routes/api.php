<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('payment-notifications', [NotificationController::class, 'index']);
