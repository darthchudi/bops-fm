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

Route::post("/bandcamp/single/fetchLink", 'BandcampController@fetchLink');

Route::post("/bandcamp/single/download", 'BandcampController@downloadSingle');

Route::get("/bandcamp/single/serveDownload", 'BandcampController@serveDownload');

Route::post("/bandcamp/album/fetchLinks", "BandcampController@fetchAlbumLinks");

Route::post('/download', 'BandcampController@download');

Route::post('/getTracklist', 'BandcampController@getTracklist');

Route::post('/getSongDetails', 'BandcampController@getSongDetails');

Route::post('/getLinks', 'BandcampController@getLinks');

Route::get('/test', 'BandcampController@test');

Route::get('/downloadToServer', 'BandcampController@downloadToServer');

Route::get('/fetchFile', 'BandcampController@fetchFile');

Route::get('/checkid3', 'BandcampController@checkid3');

Route::get('/sandbox', 'BandcampController@fetchFile');
