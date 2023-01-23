<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\CrmReportsController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


Route::apiResource('payment-notifications', NotificationController::class);

Route::get('reporte-comercial', [CrmReportsController::class, 'comercialReport']);

// Request this route only once per phase
Route::get('crear-reporte-leads', [CrmReportsController::class, 'createLeadsReport']);
Route::get('obtener-reporte-leads', [CrmReportsController::class, 'getLeadsReport']);
// Request this route to update leads on report
//Route::get('reporte-leads', [CrmReportsController::class, 'updateLeadsReport']);

// Request this route only once to fill deals report, DEAL-SELL, CATEGORY_ID 0 || DEAL-NEGOTATION, CATEGORY_ID 1
Route::get('crear-reporte-deals/{category}', [CrmReportsController::class, 'createDealReport']);
// Request this route to update deals on report, CATEGORY_ID 0 || DEAL-NEGOTATION, CATEGORY_ID 1
Route::get('actualizar-reporte-deals/{category}', [CrmReportsController::class, 'updateDealsReport']);


Route::get('procesar-lead', [LeadController::class, 'getLead']);

// insert stage status field on deals table
Route::get('deal-stage', [DealController::class, 'getDealStage']);

// Get information about bitrix and create customer user, it will be used as webhook on BITRIX24
Route::get('/bitrix-get-user', [LeadController::class, 'getDealById']);
