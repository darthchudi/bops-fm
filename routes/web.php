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

Route::post("/bandcamp/album/fetchLinks", "BandcampController@fetchAlbumLinks");

Route::post("/bandcamp/download", 'BandcampController@s3Download');

Route::post('/bandcamp/serve-user-download', 'BandcampController@serveUserDownload');

/* ----------------------- Soundcloud -------------------------*/
Route::post("/soundcloud/fetchLink", "SoundcloudController@fetchLinks");

Route::post("/soundcloud/download", "SoundcloudController@downloadSingle");

Route::post('/soundcloud/serve-user-download', "SoundcloudController@serveUserDownload");

Route::get('/s3-test', 'BandcampController@s3Test');

Route::get('/s3-meta-test', 'BandcampController@s3MetaTest');