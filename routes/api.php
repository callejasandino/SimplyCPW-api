<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientJobController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\BusinessEventController;
use App\Http\Controllers\BusinessHoursController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\GalleriesController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ShopsController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\WorkResultController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('client')->group(function () {

    Route::post('/subscribe', [SubscriberController::class, 'subscribe']);
    Route::post('/unsubscribe', [SubscriberController::class, 'unsubscribe']);
    
    Route::prefix('business-events')->group(function () {
        Route::get('/', [BusinessEventController::class, 'clientIndex']);
        Route::get('/{slug}', [BusinessEventController::class, 'show']);
        Route::get('/{slug}/discounted-services', [BusinessEventController::class, 'showDiscountedServices']);
        Route::post('/process-booking', [BusinessEventController::class, 'processClientBooking']);
    });
    
    Route::prefix('job')->group(function () {
        Route::get('/{slug}', [ClientJobController::class, 'show']);
    });

    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
    });
  
    Route::prefix('gallery')->group(function () {
        Route::get('/', [GalleriesController::class, 'index']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServicesController::class, 'index']);
    });

    // Route::prefix('quotes')->group(function () {
    //     Route::post('/', [QuoteController::class, 'store']);
    // });

    Route::prefix('work-results')->group(function () {
        Route::get('/', [WorkResultController::class, 'index']);
    });

    Route::prefix('business-hours')->group(function () {
        Route::get('/', [BusinessHoursController::class, 'index']);
    });

    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogsController::class, 'index']);
        Route::get('/{slug}', [BlogsController::class, 'show']);
    });

    Route::prefix('members')->group(function () {
        Route::get('/', [MembersController::class, 'index']);
    });

    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
    });
});


Route::prefix('admin')
    ->middleware(['user_access:admin', 'auth:sanctum'])
    ->group(function () {
    
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::post('/monthly-report', [DashboardController::class, 'generateMonthlyReport']);
    });

    Route::prefix('shops')->group(function () {
        Route::get('/', [ShopsController::class, 'index']);
        Route::get('/{uuid}', [ShopsController::class, 'show']);
        Route::post('/', [ShopsController::class, 'store']);
        Route::post('/update', [ShopsController::class, 'update']);
        Route::delete('/{uuid}', [ShopsController::class, 'destroy']);
    });

    // Route::prefix('client-jobs')->group(function () {
    //     Route::get('/', [ClientJobController::class, 'index']);
    //     Route::get('/{slug}', [ClientJobController::class, 'show']);
    //     Route::post('/', [ClientJobController::class, 'store']);
    //     Route::post('/update', [ClientJobController::class, 'update']);
    //     Route::delete('/{slug}', [ClientJobController::class, 'destroy']);
    // });

    Route::prefix('gallery')->group(function () {
        Route::get('/', [GalleriesController::class, 'index']);
        Route::post('/', [GalleriesController::class, 'store']);
        Route::delete('/{id}', [GalleriesController::class, 'destroy']);
    });

    Route::prefix('services')->group(function () {
        Route::get('/', [ServicesController::class, 'index']);
        Route::post('/', [ServicesController::class, 'store']);
        Route::post('/update', [ServicesController::class, 'update']);
        Route::delete('/{shop_uuid}/{id}', [ServicesController::class, 'destroy']);
    });

    // Route::prefix('testimonials')->group(function () {
    //     Route::get('/', [TestimonialController::class, 'index']);
    //     Route::delete('/{id}', [TestimonialController::class, 'destroy']);
    // });

    Route::prefix('quotes')->group(function () {
        Route::get('/index', [QuotesController::class, 'index']);
        Route::post('/update', [QuotesController::class, 'update']);
        Route::delete('/delete/{id}', [QuotesController::class, 'destroy']);
    });

    // Route::prefix('members')->group(function () {
    //     Route::get('/', [MembersController::class, 'index']);
    //     Route::post('/', [MembersController::class, 'store']);
    //     Route::post('/update', [MembersController::class, 'update']);
    //     Route::delete('/{id}', [MembersController::class, 'destroy']);
    // });

    // Route::prefix('work-results')->group(function () {
    //     Route::get('/', [WorkResultController::class, 'index']);
    //     Route::post('/', [WorkResultController::class, 'store']);
    //     Route::post('/update', [WorkResultController::class, 'update']);
    //     Route::delete('/{id}', [WorkResultController::class, 'destroy']);
    // });

    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogsController::class, 'index']);
        Route::post('/', [BlogsController::class, 'store']);
        Route::get('/{shop_uuid}/{slug}', [BlogsController::class, 'show']);
        Route::post('/update', [BlogsController::class, 'update']);
        Route::delete('/{shop_uuid}/{slug}', [BlogsController::class, 'destroy']);
    });

    // Route::prefix('business-hours')->group(function () {
    //     Route::get('/', [BusinessHoursController::class, 'index']);
    //     Route::post('/', [BusinessHoursController::class, 'store']);
    // });

    // Route::prefix('business-events')->group(function () {
    //     Route::get('/', [BusinessEventController::class, 'index']);
    //     Route::post('/', [BusinessEventController::class, 'store']);
    //     Route::get('/{slug}', [BusinessEventController::class, 'show']);
    //     Route::post('/update', [BusinessEventController::class, 'update']);
    //     Route::delete('/{id}', [BusinessEventController::class, 'delete']); 
    // });
});