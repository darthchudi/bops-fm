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

Route::post('/download', 'BandcampController@download');

Route::post('/getTracklist', 'BandcampController@getTracklist');

Route::post('/getSongDetails', 'BandcampController@getSongDetails');

Route::post('/getLinks', 'BandcampController@getLinks');

Route::get('/test', 'BandcampController@test');

Route::post('/determineLink', 'BandcampController@determineLink');

Route::get('/downloadToServer', 'BandcampController@downloadToServer');

Route::get('/fetchFile', 'BandcampController@fetchFile');

Route::get('/checkid3', 'BandcampController@checkid3');

Route::get('/sandbox', 'BandcampController@sandbox');
