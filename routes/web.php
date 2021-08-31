<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/user/reset-password', function () {
    return view('auth.passwords.reset');
});

Route::get('/user/reset-password/success', function () {
    return view('auth.register');
});

Route::get('/user/reset-password/error', function () {
    return view('auth.login');
});


