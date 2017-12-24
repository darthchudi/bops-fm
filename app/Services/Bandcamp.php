<?php

namespace App\Services;
use JonnyW\PhantomJs\Client as Client;
use Symfony\Component\DomCrawler\Crawler as Crawler;


class Bandcamp{

	public function getPage($url){
		$html = file_get_contents($url);
	  	preg_match_all('/"mp3-128":"(.*?)"/', $html, $array);
		return $array[1];
	}

	public function downloadSong($songUrl){
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec ($ch);
        curl_close ($ch);

        $name = 'eba';
	  	$downloadPath = __DIR__."/../../downloads/".$name.".mp3";
        $download = file_put_contents($downloadPath, $song);
        if(! $download){
        	return false;
        }
        else
        	return $song;
    }

    public function getDetails(){
    	$client = Client::getInstance();
    	$client->getEngine()->setPath('../vendor/bin/phantomjs');
    	$request = $client->getMessageFactory()->createRequest('https://miloraps.bandcamp.com/album/who-told-you-to-think', 'GET');
    	$response = $client->getMessageFactory()->createResponse();
    	$client->send($request, $response);
    	$doc = new \DOMDocument();
    	libxml_use_internal_errors(true);
    	$doc->loadHTML($response->getContent());
    	libxml_use_internal_errors(false);
    	$html = $doc->saveHTML();

    	$crawler = new Crawler($html);
    	echo $name = $crawler->filterXPath('/html/body/div[5]/div/div[1]/div[2]/div[1]/h2')->text();
    }

    public function id3($path){
    	include_once('../vendor/getid3/getid3.php');
    	$getID3 = new \getID3;
    	$info = $getID3->analyze($path);
    	dd($info);
    }

    public function isAlbum($url){
    	$isAlbum = preg_match('/(bandcamp.com\/album\/)/i', $url, $matches);
    	// dd($matches);
    	return $isAlbum;
    }

    public function isSong($url){
    	$isSong = preg_match('/(bandcamp.com\/track\/)/i', $url, $matches);
    	return $isSong;
    }


}