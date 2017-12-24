<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Symfony\Component\DomCrawler\Crawler;
use JonnyW\PhantomJs\Client as Client;


class BandcampController extends Controller
{
    public function getPage(Request $request){
    	//Get the Bandcamp Page URL
    	$html = file_get_contents($request->url);
	  	preg_match_all('/"mp3-128":"(.*?)"/', $html, $array);
	  	$songs[] = $array[1];

	  	$this->downloadSong($songs[0][2]);
    }

    public function downloadSong($songUrl){
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec ($ch);
        curl_close ($ch);

        $name = 'chudi';
	  	$downloadPath = __DIR__."/../../../downloads/".$name.".mp3";
        $download = file_put_contents($downloadPath, $song);
        if(! $download){
        	return false;
        }
        else
        	return true;
    }
}