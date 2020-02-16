<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('verify', 'AuthController@verify');
    Route::put('resend_verify_code', 'AuthController@resend_verify_code');
});

Route::group(['prefix' => 'users'], function () {
    Route::post('upload_avatar', 'UsersController@upload_avatar');
    Route::post('invited_events', 'UsersController@invited_events');
    Route::post('user_events', 'UsersController@user_events');
});

Route::group(['prefix' => 'events'], function () {
    Route::post('create', 'EventController@create');
});


Route::group(['prefix' => 'invitation'], function () {
    Route::post('invited_users', 'InvitationController@invited_users');
    Route::post('update', 'InvitationController@update');
});



