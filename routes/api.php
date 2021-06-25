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
        Route::post('order', [App\Http\Controllers\UserController::class, 'order']);
        Route::middleware(['auth'])->group(function () {
            Route::get('history', [App\Http\Controllers\UserController::class, 'getUserHistory']);
            Route::post('address', [App\Http\Controllers\UserController::class, 'saveAddress']);
            Route::get('address', [App\Http\Controllers\UserController::class, 'getSavedAddresses']);
        });

    });
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
        Route::post('signup', [App\Http\Controllers\UserController::class, 'signup']);
        Route::post('login', [App\Http\Controllers\UserController::class, 'login']);

        Route::middleware(['auth'])->group(function () {
            Route::post('pause-account', [App\Http\Controllers\UserController::class, 'pauseAccount']);
            Route::get('history', [App\Http\Controllers\UserController::class, 'getPartnerHistory']);
            Route::post('top-partner', [App\Http\Controllers\UserController::class, 'makeTopPartner']);

            Route::prefix('profile')->group(function () {
                Route::post('/', [App\Http\Controllers\UserController::class, 'profile']);
                Route::post('update', [App\Http\Controllers\UserController::class, 'updateProfile']);
            });
            Route::prefix('vehicle')->group(function () {
                Route::post('add', [App\Http\Controllers\UserController::class, 'addVehicle']);
                Route::get('update', [App\Http\Controllers\UserController::class, 'updateVehicle']);
                Route::post('disable', [App\Http\Controllers\UserController::class, 'disableVehicle']);
                Route::get('/', [App\Http\Controllers\UserController::class, 'getVehicles']);
                Route::get('/{id}', [App\Http\Controllers\UserController::class, 'getVehicle']);
            });
            Route::prefix('rider')->group(function () {
                Route::post('dismiss/{id}', [App\Http\Controllers\UserController::class, 'dismissRider']);
                Route::post('update/{id}', [App\Http\Controllers\UserController::class, 'updateRider']);
                Route::get('order/{id}', [App\Http\Controllers\UserController::class, 'ordersDoneByRider']);
                Route::post('/', [App\Http\Controllers\UserController::class, 'createRider']);
                Route::post('disable/{id}', [App\Http\Controllers\UserController::class, 'disableRider']);
                Route::get('/', [App\Http\Controllers\UserController::class, 'getRiders']);
                Route::post('assign/{id}', [App\Http\Controllers\UserController::class, 'assignOrder']);
            });
            Route::prefix('order')->group(function () {
                Route::get('/all', [App\Http\Controllers\UserController::class, 'getOrders']);
                Route::get('{id}', [App\Http\Controllers\UserController::class, 'getOneOrder']);
            });

            Route::prefix('route')->group(function () {
                Route::post('/set', [App\Http\Controllers\UserController::class, 'setRouteCosting']);
                Route::post('/update/{id}', [App\Http\Controllers\UserController::class, 'updateRouteCosting']);
            });
            
            Route::post('subscribe', [App\Http\Controllers\UserController::class, 'subscribe']);
            Route::post('ophours/add', [App\Http\Controllers\UserController::class, 'addOperatingHours']);
            Route::post('ophours/update', [App\Http\Controllers\UserController::class, 'updateOperatingHours']);
           
        });

    });



