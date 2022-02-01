<?php

use App\Http\Controllers\CrmReportsController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


Route::apiResource('payment-notifications', NotificationController::class);

Route::get('reporte-comercial', [CrmReportsController::class, 'comercialReport']);

// Request this route only once per phase
Route::get('crear-reporte-leads/{phase}', [CrmReportsController::class, 'createLeadsReport']);
// Request this route to update leads on report
Route::get('actualizar-reporte-leads', [CrmReportsController::class, 'updateLeadsReport']);

// Request this route only once to fill deals report, DEAL-SELL, CATEGORY_ID 0 || DEAL-NEGOTATION, CATEGORY_ID 1
Route::get('crear-reporte-deals/{category}', [CrmReportsController::class, 'createDealReport']);
// Request this route to update deals on report, CATEGORY_ID 0 || DEAL-NEGOTATION, CATEGORY_ID 1
Route::get('actualizar-reporte-deals/{category}', [CrmReportsController::class, 'updateDealsReport']);
