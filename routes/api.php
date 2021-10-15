<?php

use App\Http\Controllers\CrmReportsController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


Route::apiResource('payment-notifications', NotificationController::class);

Route::get('reporte-comercial', [CrmReportsController::class, 'comercialReport']);
Route::get('crear-reporte-leads/{phase}', [CrmReportsController::class, 'createLeadsReport']);
