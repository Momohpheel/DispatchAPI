<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('auth')->group(function () {
        Route::prefix('user')->group(function () {
            Route::post('onboard', [App\Http\Controllers\UserController::class, 'onboard']);
            Route::post('register', [App\Http\Controllers\UserController::class, 'profile']);
            Route::post('login', [App\Http\Controllers\UserController::class, 'login']);

        });
    });

    Route::prefix('user')->group(function () {
        // Route::middleware(['auth:api'])->group(function () {
            Route::post('order/{id}', [App\Http\Controllers\UserController::class, 'order']);
            Route::get('history', [App\Http\Controllers\UserController::class, 'getUserHistory']);
            Route::post('address', [App\Http\Controllers\UserController::class, 'saveAddress']);
            Route::get('address', [App\Http\Controllers\UserController::class, 'getSavedAddresses']);
        // });
    });


    // Route::prefix('rider')->group(function () {
    //     Route::post('signup', [App\Http\Controllers\UserController::class, 'onboard']);
    //     Route::post('login', [App\Http\Controllers\UserController::class, 'profile']);
    //     Route::post('login', [App\Http\Controllers\UserController::class, 'login']);
    //     Route::post('order', [App\Http\Controllers\UserController::class, 'order']);
    //     Route::middleware(['auth'])->group(function () {
    //         Route::get('history', [App\Http\Controllers\UserController::class, 'getUserHistory']);
    //         Route::post('address', [App\Http\Controllers\UserController::class, 'saveAddress']);
    //         Route::get('address', [App\Http\Controllers\UserController::class, 'getSavedAddresses']);
    //     });

    // });



    Route::prefix('partner')->group(function () {
        Route::post('signup', [App\Http\Controllers\PartnerController::class, 'signup']);
        Route::post('login', [App\Http\Controllers\PartnerController::class, 'login']);

        Route::middleware(['auth:partner'])->group(function () {
            Route::post('pause-account', [App\Http\Controllers\PartnerController::class, 'pauseAccount']);
            Route::get('history', [App\Http\Controllers\PartnerController::class, 'getPartnerHistory']);
            Route::post('top-partner', [App\Http\Controllers\PartnerController::class, 'makeTopPartner']);

            Route::prefix('profile')->group(function () {
                Route::post('/', [App\Http\Controllers\PartnerController::class, 'profile']);
                Route::post('update', [App\Http\Controllers\PartnerController::class, 'updateProfile']);
            });
            Route::prefix('vehicle')->group(function () {
                Route::post('add', [App\Http\Controllers\PartnerController::class, 'addVehicle']);
                Route::get('update', [App\Http\Controllers\PartnerController::class, 'updateVehicle']);
                Route::post('disable/{id}', [App\Http\Controllers\PartnerController::class, 'disableVehicle']);
                Route::get('/', [App\Http\Controllers\PartnerController::class, 'getVehicles']);
                Route::get('/{id}', [App\Http\Controllers\PartnerController::class, 'getVehicle']);
            });
            Route::prefix('rider')->group(function () {
                Route::post('dismiss/{id}', [App\Http\Controllers\PartnerController::class, 'dismissRider']);
                Route::post('update/{id}', [App\Http\Controllers\PartnerController::class, 'updateRider']);
                Route::get('order/{id}', [App\Http\Controllers\PartnerController::class, 'ordersDoneByRider']);
                Route::post('/', [App\Http\Controllers\PartnerController::class, 'createRider']);
                Route::post('disable/{id}', [App\Http\Controllers\PartnerController::class, 'disableRider']);
                Route::get('/', [App\Http\Controllers\PartnerController::class, 'getRiders']);
                Route::post('assign/{id}', [App\Http\Controllers\PartnerController::class, 'assignOrder']);
            });
            Route::prefix('order')->group(function () {
                Route::get('/all', [App\Http\Controllers\PartnerController::class, 'getOrders']);
                Route::get('{id}', [App\Http\Controllers\PartnerController::class, 'getOneOrder']);
            });

            Route::prefix('route')->group(function () {
                Route::post('/set', [App\Http\Controllers\PartnerController::class, 'setRouteCosting']);
                Route::post('/update/{id}', [App\Http\Controllers\PartnerController::class, 'updateRouteCosting']);
            });

            Route::post('subscribe', [App\Http\Controllers\PartnerController::class, 'subscribe']);
            Route::post('ophours/add', [App\Http\Controllers\PartnerController::class, 'addOperatingHours']);
            Route::post('ophours/update', [App\Http\Controllers\PartnerController::class, 'updateOperatingHours']);

        });

    });



