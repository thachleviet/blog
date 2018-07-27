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

Route::get('/', function () {
    return view('welcome');
});


Route::group(['middleware' => ['web'], 'prefix' => '' ], function() {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/install', 'AppController@installApp')->name('app');
    Route::get('/submitInstall', 'AppController@submitInstall')->name('submit_install');
    Route::get('/auth', 'AppController@auth')->name('auth');
});


Route::group(['middleware' => ['web'], 'prefix' => 'product' ], function() {
    Route::get('/', 'ProductController@index')->name('product');
    Route::get('/{id}', 'ProductController@edit')->name('product.edit');
    Route::put('/{id}', 'ProductController@submitEdit')->name('product.submit_edit');
});