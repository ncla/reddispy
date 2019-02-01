<?php

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

Route::get('/', function (Illuminate\Support\MessageBag $bag) {
    dump(Auth::user());

    return view('welcome');
});

Route::get('/account/login', 'Auth\LoginController@login');
Route::get('/account/callback', 'Auth\RegisterController@callback');
Route::get('/account/logout', 'Auth\LoginController@logout');