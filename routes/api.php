<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeneratedMessageController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicContentController;
use App\Http\Controllers\Api\ServiceRequestPublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIK — tanpa auth, dipakai oleh website publik
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:admin-login');
Route::post('/requests', [ServiceRequestPublicController::class, 'store'])->middleware('throttle:public-form');
Route::get('/portfolios', [PublicContentController::class, 'portfolios']);
Route::get('/portfolios/{slug}', [PublicContentController::class, 'portfolio']);
Route::get('/company-profile', [PublicContentController::class, 'companyProfile']);

/*
|--------------------------------------------------------------------------
| ADMIN — wajib login (Sanctum token) + role admin/staff
|--------------------------------------------------------------------------
| Session timeout diatur lewat config sanctum.expiration (lihat README).
*/
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/requests', [ServiceRequestController::class, 'index']);
        Route::get('/requests/{request}', [ServiceRequestController::class, 'show']);
        Route::patch('/requests/{request}', [ServiceRequestController::class, 'update']);
        Route::patch('/requests/{request}/status', [ServiceRequestController::class, 'changeStatus']);
        Route::post('/requests/{request}/notes', [ServiceRequestController::class, 'addNote']);
        Route::post('/requests/{request}/convert-to-client', [ServiceRequestController::class, 'convertToClient']);
        Route::post('/requests/{request}/generate-message', [GeneratedMessageController::class, 'preview']);
        Route::post('/requests/{request}/messages', [GeneratedMessageController::class, 'store']);
        Route::patch('/messages/{message}/mark-sent', [GeneratedMessageController::class, 'markSent']);

        Route::apiResource('clients', ClientController::class)->except(['show'])->parameters(['clients' => 'client']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);

        Route::get('/portfolios', [AdminPortfolioController::class, 'index']);
        Route::post('/portfolios', [AdminPortfolioController::class, 'store']);
        Route::patch('/portfolios/{portfolio}', [AdminPortfolioController::class, 'update']);
        Route::patch('/portfolios/{portfolio}/publish', [AdminPortfolioController::class, 'togglePublish']);
        Route::delete('/portfolios/{portfolio}', [AdminPortfolioController::class, 'destroy']);

        Route::get('/company-profile', [CompanyProfileController::class, 'show']);
        Route::patch('/company-profile', [CompanyProfileController::class, 'update']);

        Route::apiResource('templates', TemplateController::class)->except(['show']);

        Route::get('/activity-log', [ActivityLogController::class, 'index']);
    });
});
