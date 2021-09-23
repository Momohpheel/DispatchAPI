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
            Route::post('forgot-password', [App\Http\Controllers\UserController::class, 'forgotPassword']);
            Route::post('reset-password/{token}', [App\Http\Controllers\UserController::class, 'resetPassword']);
        });
    });

    Route::prefix('user')->group(function () {
        Route::middleware(['auth:api'])->group(function () {
            Route::prefix('profile')->group(function () {
                Route::post('update', [App\Http\Controllers\UserController::class, 'updateProfile']);
                Route::post('image', [App\Http\Controllers\UserController::class, 'uploadImage']);
                Route::get('/', [App\Http\Controllers\UserController::class, 'getProfile']);
            });
            Route::post('order/{id}', [App\Http\Controllers\UserController::class, 'order']);
            //Route::get('history', [App\Http\Controllers\UserController::class, 'getUserHistory']);
            Route::post('address', [App\Http\Controllers\UserController::class, 'saveAddress']);
            Route::get('address', [App\Http\Controllers\UserController::class, 'getSavedAddresses']);
            Route::get('total/{id}', [App\Http\Controllers\UserController::class, 'count']);

            Route::get('dashboard/{id}', [App\Http\Controllers\UserController::class, 'dashboard']);

            Route::post('logout', [App\Http\Controllers\UserController::class, 'logout']);
            Route::delete('dropoff/{id}', [App\Http\Controllers\UserController::class, 'cancelDropOff']);
            Route::get('order/{id}', [App\Http\Controllers\UserController::class, 'getOrder']);
            Route::get('orders/{id}', [App\Http\Controllers\UserController::class, 'getAllOrders']);

            Route::get('history/{id}', [App\Http\Controllers\UserController::class, 'orderHistory']);

            Route::get('histories', [App\Http\Controllers\UserController::class, 'allOrderHistory']);

            Route::get('dropoff/{id}', [App\Http\Controllers\UserController::class, 'getOneDropoff']);

            Route::get('vehicle/available/{id}', [App\Http\Controllers\UserController::class, 'checkVehiclesAvailablePerPartner']);

            Route::post('payment/log', [App\Http\Controllers\UserController::class, 'payment']);
            Route::post('order/status/{status}', [App\Http\Controllers\UserController::class, 'getOrderByStatus']);

            Route::get('transactions', [App\Http\Controllers\UserController::class, 'getTransactionHistory']);
            Route::get('transactions/all', [App\Http\Controllers\UserController::class, 'getAllTransactionHistory']);
        });
    });


    Route::prefix('rider')->group(function () {

        Route::post('login', [App\Http\Controllers\RiderController::class, 'login']);
        Route::middleware(['auth:rider'])->group(function () {
            Route::post('order/start/{id}', [App\Http\Controllers\RiderController::class, 'start_order']);
            Route::post('order/end/{id}', [App\Http\Controllers\RiderController::class, 'end_order']);
            Route::get('/', [App\Http\Controllers\RiderController::class, 'getProfile']);
            Route::get('orders', [App\Http\Controllers\RiderController::class, 'getOrders']);
            Route::get('history', [App\Http\Controllers\RiderController::class, 'history']);
            Route::put('phone/update', [App\Http\Controllers\RiderController::class, 'updatePhone']);
            Route::post('location/set', [App\Http\Controllers\RiderController::class, 'setDriverLocation']);

            Route::get('dashboard/{id}', [App\Http\Controllers\RiderController::class, 'dashboard']);
            Route::get('order/status/{status}', [App\Http\Controllers\RiderController::class, 'getOrderByStatus']);

            Route::post('order/status/{id}', [App\Http\Controllers\RiderController::class, 'changeOrderStatus']);
        });

    });



    Route::prefix('partner')->group(function () {
        Route::post('signup', [App\Http\Controllers\PartnerController::class, 'signup']);
        Route::post('login', [App\Http\Controllers\PartnerController::class, 'login']);

        Route::get('all', [App\Http\Controllers\PartnerController::class, 'allPartner']);
        Route::get('top/all', [App\Http\Controllers\PartnerController::class, 'allTopPartner']);

        Route::post('forgot-password', [App\Http\Controllers\User\AuthController::class, 'forgotPassword']);
        Route::post('reset-password/{token}', [App\Http\Controllers\User\AuthController::class, 'resetPassword']);
        Route::get('subscription', [App\Http\Controllers\PartnerController::class, 'subscription']);


        Route::middleware(['auth:partner'])->group(function () {
            Route::post('pause-account', [App\Http\Controllers\PartnerController::class, 'pauseAccount']);
            Route::get('history', [App\Http\Controllers\PartnerController::class, 'getPartnerHistory']);
            Route::post('top-partner', [App\Http\Controllers\PartnerController::class, 'makeTopPartner']);

            Route::get('dashboard', [App\Http\Controllers\PartnerController::class, 'dashboard']);

            Route::get('sub-details', [App\Http\Controllers\PartnerController::class, 'subscriptionDetails']);
            Route::post('earnings', [App\Http\Controllers\PartnerController::class, 'PartnerEarnings']);

            Route::prefix('profile')->group(function () {
                Route::post('/', [App\Http\Controllers\PartnerController::class, 'profile']);
                Route::post('update', [App\Http\Controllers\PartnerController::class, 'updateProfile']);
            });
            Route::prefix('vehicle')->group(function () {
                Route::post('add', [App\Http\Controllers\PartnerController::class, 'addVehicle']);
                Route::put('update/{id}', [App\Http\Controllers\PartnerController::class, 'updateVehicle']);
                Route::post('disable/{id}', [App\Http\Controllers\PartnerController::class, 'disableVehicle']);
                Route::get('/', [App\Http\Controllers\PartnerController::class, 'getVehicles']);
                Route::get('/{id}', [App\Http\Controllers\PartnerController::class, 'getVehicle']);
                Route::get('count/{id}', [App\Http\Controllers\PartnerController::class, 'countForVehicle']);
                Route::get('orders', [App\Http\Controllers\PartnerController::class, 'getOrderbyVehicle']);
                Route::post('earnings/{id}', [App\Http\Controllers\PartnerController::class, 'VehicleEarnings']);
                Route::get('/plate/number', [App\Http\Controllers\PartnerController::class, 'allPlateNumbers']);

            });

            Route::prefix('rider')->group(function () {
                Route::post('dismiss/{id}', [App\Http\Controllers\PartnerController::class, 'dismissRider']);
                Route::post('update/{id}', [App\Http\Controllers\PartnerController::class, 'updateRider']);
                Route::get('order/{id}', [App\Http\Controllers\PartnerController::class, 'ordersDoneByRider']);
                Route::post('/', [App\Http\Controllers\PartnerController::class, 'createRider']);
                Route::post('disable/{id}', [App\Http\Controllers\PartnerController::class, 'disableRider']);
                Route::get('/', [App\Http\Controllers\PartnerController::class, 'getRiders']);
                Route::get('/{id}', [App\Http\Controllers\PartnerController::class, 'getRider']);
                Route::post('assign', [App\Http\Controllers\PartnerController::class, 'assignOrder']);
                Route::post('earnings/{id}', [App\Http\Controllers\PartnerController::class, 'RiderEarnings']);

            });
            Route::prefix('order')->group(function () {
                Route::get('/all', [App\Http\Controllers\PartnerController::class, 'getOrders']);
                Route::get('/{id}', [App\Http\Controllers\PartnerController::class, 'getOneOrder']);
                Route::get('/status/{status}', [App\Http\Controllers\PartnerController::class, 'getOrderByStatus']);
                Route::get('/pending/all', [App\Http\Controllers\PartnerController::class, 'pendingOrders']);
            });

            Route::prefix('route')->group(function () {
                Route::post('/set', [App\Http\Controllers\PartnerController::class, 'setRouteCosting']);
                Route::post('/update/{id}', [App\Http\Controllers\PartnerController::class, 'updateRouteCosting']);
                Route::get('/', [App\Http\Controllers\PartnerController::class, 'getRouteCost']);

            });

            Route::get('count', [App\Http\Controllers\PartnerController::class, 'count']);

            Route::post('subscribe', [App\Http\Controllers\PartnerController::class, 'subscribe']);
            Route::post('ophours/add', [App\Http\Controllers\PartnerController::class, 'addOperatingHours']);
            Route::post('ophours/update/{id}', [App\Http\Controllers\PartnerController::class, 'updateOperatingHours']);
            Route::get('ophours', [App\Http\Controllers\PartnerController::class, 'getOperatingHour']);



            Route::post('payment/log', [App\Http\Controllers\PartnerController::class, 'payment']);
            Route::get('payout', [App\Http\Controllers\PartnerController::class, 'getPayoutLog']);
        });

    });




    Route::prefix('admin')->group(function () {

        Route::post('signup', [App\Http\Controllers\AdminController::class, 'signup']);
        Route::post('login', [App\Http\Controllers\AdminController::class, 'login']);

        Route::middleware(['auth:admin'])->group(function () {
            Route::get('dashboard', [App\Http\Controllers\AdminController::class, 'dashboard']);

            Route::get('user/all', [App\Http\Controllers\AdminController::class, 'allUsers']);
            Route::get('partner/all', [App\Http\Controllers\AdminController::class, 'allPartners']);
            Route::get('partner/top', [App\Http\Controllers\AdminController::class, 'allTopPartners']);
            Route::get('partner/disable/{id}', [App\Http\Controllers\AdminController::class, 'disablePartner']);
            Route::get('partner/rider/{id}', [App\Http\Controllers\AdminController::class, 'ridersByPartner']);
            Route::get('partner/order/{id}', [App\Http\Controllers\AdminController::class, 'ordersByPartner']);
            Route::get('order', [App\Http\Controllers\AdminController::class, 'allOrders']);

            Route::get('user/{id}', [App\Http\Controllers\AdminController::class, 'oneUser']);
            Route::get('user/order/{id}', [App\Http\Controllers\AdminController::class, 'usersOrders']);

            Route::get('partner/{id}', [App\Http\Controllers\AdminController::class, 'onePartner']);
            Route::get('order/{id}', [App\Http\Controllers\AdminController::class, 'oneOrder']);

            Route::get('rider/order/{id}', [App\Http\Controllers\AdminController::class, 'ridersOrders']);

            Route::get('partner/vehicle/{id}', [App\Http\Controllers\AdminController::class, 'getVehicles']);

            Route::post('order/status/{id}', [App\Http\Controllers\AdminController::class, 'changeOrderStatus']);

            // Route::put('phone/update', [App\Http\Controllers\AdminController::class, 'updatePhone']);

            // Route::get('dashboard/{id}', [App\Http\Controllers\AdminController::class, 'dashboard']);
            // Route::get('order/status/{status}', [App\Http\Controllers\AdminController::class, 'getOrderByStatus']);

            // Route::post('order/status/{id}', [App\Http\Controllers\AdminController::class, 'changeOrderStatus']);
        });

    });
