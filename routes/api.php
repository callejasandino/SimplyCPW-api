<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientJobController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\WorkResultController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('client')->group(function () {
    Route::prefix('jobs')->group(function () {
        Route::get('/{slug}', [ClientJobController::class, 'show']);
    });

    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
    });
  
    Route::prefix('gallery')->group(function () {
        Route::get('/', [GalleryController::class, 'index']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
    });

    Route::prefix('quotes')->group(function () {
        Route::post('/', [QuoteController::class, 'store']);
    });

    Route::prefix('work-results')->group(function () {
        Route::get('/', [WorkResultController::class, 'index']);
    });
});


Route::prefix('admin')
    ->middleware(['user_access:admin', 'auth:sanctum'])
    ->group(function () {
    
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('client-jobs')->group(function () {
        Route::get('/', [ClientJobController::class, 'index']);
        Route::get('/{slug}', [ClientJobController::class, 'show']);
        Route::post('/', [ClientJobController::class, 'store']);
        Route::post('/update', [ClientJobController::class, 'update']);
        Route::delete('/{id}', [ClientJobController::class, 'destroy']);
    });

    Route::prefix('gallery')->group(function () {
        Route::get('/', [GalleryController::class, 'index']);
        Route::post('/', [GalleryController::class, 'store']);
        Route::put('/{id}', [GalleryController::class, 'update']);
        Route::delete('/{id}', [GalleryController::class, 'destroy']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/{id}', [ServiceController::class, 'show']);
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::post('/update', [ServiceController::class, 'update']);
        Route::delete('/{id}', [ServiceController::class, 'destroy']);
    });

    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
        Route::delete('/{id}', [TestimonialController::class, 'destroy']);
    });

    Route::prefix('quotes')->group(function () {
        Route::get('/', [QuoteController::class, 'index']);
        Route::post('/update', [QuoteController::class, 'update']);
        Route::delete('/{id}', [QuoteController::class, 'destroy']);
    });

    Route::prefix('equipments')->group(function () {
        Route::get('/', [EquipmentController::class, 'index']);
        Route::post('/', [EquipmentController::class, 'store']);
        Route::post('/update', [EquipmentController::class, 'update']);
        Route::delete('/{id}', [EquipmentController::class, 'destroy']);
    });

    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);
        Route::post('/update', [MemberController::class, 'update']);
        Route::delete('/{id}', [MemberController::class, 'destroy']);
    });

    Route::prefix('work-results')->group(function () {
        Route::get('/', [WorkResultController::class, 'index']);
        Route::post('/', [WorkResultController::class, 'store']);
        Route::post('/update', [WorkResultController::class, 'update']);
        Route::delete('/{id}', [WorkResultController::class, 'destroy']);
    });

    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index']);
        Route::post('/', [BlogController::class, 'store']);
        Route::get('/{slug}', [BlogController::class, 'show']);
        Route::post('/', [BlogController::class, 'update']);
        Route::delete('/{slug}', [BlogController::class, 'destroy']);
    });
});