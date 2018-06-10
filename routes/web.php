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

Route::get('/about', function(){
	return view('about');
});

Route::get('/collab', function(){
	return view('collab');
});

Route::get('/likes', function(){
	return view('soundcloud-likes');
});

/* ---------------------------------------------------- API ROUTES -------------------------------------------*/

/* ----------------------- Bandcamp -------------------------*/
Route::post("/bandcamp/single/fetchLink", 'BandcampController@fetchLink');

Route::post("/bandcamp/album/fetchLinks", "BandcampController@fetchAlbumLinks");

Route::post("/bandcamp/download", 'BandcampController@downloadSingle');

Route::post('/bandcamp/serve-user-download', 'BandcampController@serveUserDownload');

/* ----------------------- Soundcloud -------------------------*/
Route::post("/soundcloud/fetchLink", "SoundcloudController@fetchLinks");

Route::post("/soundcloud/download", "SoundcloudController@downloadSingle");

Route::post('/soundcloud/serve-user-download', "SoundcloudController@serveUserDownload");

Route::post('soundcloud/likes', 'SoundcloudController@fetchLikes');

/* ----------------------- Zip -------------------------*/
Route::post('/make-zip', 'ZipController@makeZip');

Route::post('/download-zip', 'ZipController@downloadZip');




// Route::get('/s3-test', 'BandcampController@s3Test');

// Route::get('/s3-meta-test', 'BandcampController@s3MetaTest');