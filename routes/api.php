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

Route::group(['prefix' => 'auth', 'namespace' => 'API\Auth'], function () {
    Route::post('login', 'LoginController@loginUser');
    Route::post('register', 'RegisterController@registerUser');

    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
});

Route::group(['prefix' => 'auth', 'middleware' => 'auth:api', 'namespace' => 'API\Auth'], function () {
    Route::post('logout', 'LoginController@logoutUser');
});
