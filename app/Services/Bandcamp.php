<?php

namespace App\Services;
use JonnyW\PhantomJs\Client as Client;
use Symfony\Component\DomCrawler\Crawler as Crawler;


class Bandcamp{

	public function isAlbum($url){
    	$isAlbum = preg_match('/(bandcamp.com\/album\/)/i', $url, $matches);
    	// dd($matches);
    	return $isAlbum;
    }

    public function isSong($url){
    	$isSong = preg_match('/(bandcamp.com\/track\/)/i', $url, $matches);
    	return $isSong;
    }

	public function getLinks($url){
		$html = file_get_contents($url);
	  	preg_match_all('/"mp3-128":"(.*?)"/', $html, $array);
		return $array[1];
	}

	public function getAlbumDetails($url){
		$details = array();
    	$html = file_get_contents($url);
    	$crawler = new Crawler($html);
    	$title = $crawler->filterXpath('//title')->text();
    	list($details['album_name']) = explode('|', $title);
    	$details['artiste'] = $crawler->filterXpath('//span[@itemprop="byArtist"]//a')->text();
    	$details['tracklist'] = $crawler->filterXpath('//span[@itemprop="name"]')->each(function(Crawler $node, $i){
    		return $node->text();
    	});

    	$details['cover_art']= $crawler->filterXpath('//div[@id="tralbumArt"]//a/@href')->text();
    	
    	return $details;
    }

    public function getSongDetails($url){
    	$details = array();
    	$html = file_get_contents($url);
    	$crawler = new Crawler($html);

    	$title = $crawler->filterXpath('//title')->text();
    	list($details['song_name']) = explode('|', $title);
    	$details['artiste'] = $crawler->filterXpath('//span[@itemprop="byArtist"]//a')->text();
    	$details['album'] = $crawler->filterXpath('//a//span[@itemprop="name"]')->text();
    	$details['cover_art']= $crawler->filterXpath('//div[@id="tralbumArt"]//a/@href')->text();
    	return $details;
    }

	public function downloadSong($songUrl, $details){
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec($ch);
        curl_close ($ch);

        $name = $details['artiste'].' - '.$details['song_name'];
        
        $this->id3('../downloads/eba.mp3');

	  	$downloadPath = __DIR__."/../../downloads/".$name.".mp3";
        $download = file_put_contents($downloadPath, $song);
        if(!$download){
        	return false;
        }
        else
        	return true;
    }

    public function downloadToServer($songUrl, $details){
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $songUrl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$song = curl_exec($ch);
    	curl_close($ch);
    	$name = $details['artiste'].' - '.$details['song_name'];
    	$path = '/../../storage/tmp/'.$name.'.mp3';
    	$downloadPath = __DIR__.$path;
    	$download = file_put_contents($downloadPath, $song);
    	if($download){
    		return $name;
    	}
    	else
    		return false;
    }	

    public function id3($path){
    	include_once('../vendor/getid3/getid3.php');
    	$getID3 = new \getID3;
    	$info = $getID3->analyze($path);
    	dd($info);
    }


}