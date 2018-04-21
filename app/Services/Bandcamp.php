<?php

namespace App\Services;
use JonnyW\PhantomJs\Client as Client;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use Carbon\Carbon;

class Bandcamp{
    public $page;

	public function isAlbum($url){
    	$isAlbum = preg_match('/(bandcamp.com\/album\/)/i', $url, $matches);
    	return $isAlbum;
    }

    public function isSong($url){
    	$isSong = preg_match('/(bandcamp.com\/track\/)/i', $url, $matches);
    	return $isSong;
    }

	public function getLinks($url){
		$this->page = file_get_contents($url);
	  	preg_match_all('/"mp3-128":"(.*?)"/', $this->page, $array);
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

    public function getSongDetails(){
    	$details = array();
    	$crawler = new Crawler($this->page);

    	$title = $crawler->filterXpath('//title')->text();
    	list($details['song_name']) = explode('|', $title);
    	$details['artiste'] = $crawler->filterXpath('//span[@itemprop="byArtist"]//a')->text();
        //Fetch the song album
        if($crawler->filterXpath('//a//span[@itemprop="name"]')->count()){
            $details['album'] = $crawler->filterXpath('//a//span[@itemprop="name"]')->text();
        }
        else{
            $details['album'] = $details['song_name'].'— Single ';
        }
    	
    	$details['cover_art']= $crawler->filterXpath('//div[@id="tralbumArt"]//a/@href')->text();
    	return $details;
    }

	public function serverDownload($songUrl, $details){
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec($ch);
        curl_close ($ch);
        $dateFolder = storage_path().'/tmp/General/';
        $presentDate = Carbon::now()->toFormattedDateString();

        if(is_dir(storage_path().'/tmp/'.$presentDate)){
            $dateFolder = storage_path()."/tmp/$presentDate";

        } else{
            $createdDirectory = mkdir(storage_path()."/tmp/$presentDate", 0700);
            if($createdDirectory){
                $dateFolder = storage_path()."/tmp/$presentDate";
            }
        }

        if(strpos($details['album'], '<') || strpos($details['album'], '>')  || strpos($details['song_name'], '<') || $strpos($details['song_name'], '>')){
            $details = $this->sanitize($details);     
        }

        $songFolder = $dateFolder.'/'.$details['artiste'].' - '.$details['album'];
        mkdir($songFolder, 0700);
        $name = $details['artiste'].' - '.$details['song_name'].'.mp3';
        
        // $this->id3('../downloads/eba.mp3');

        $downloadPath = "$songFolder/$name";
        $download = file_put_contents($downloadPath, $song);
        if(!$download){
            return false;
        }
        else
            return $downloadPath;
    }

    public function sanitize($details){
        $unwantedCharacters = ["<", ">", "/", "\\", "/"];
        $details['album'] = str_replace($unwantedCharacters, '', $details['album']);
        $details['song_name'] = str_replace($unwantedCharacters, '', $details['song_name']);
        return $details;
    }

    // public function downloadToServer($songUrl, $name){
    //  $ch = curl_init();
    //  curl_setopt($ch, CURLOPT_URL, $songUrl);
    //  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //  $song = curl_exec($ch);
    // 	curl_close($ch);
    // 	$downloadPath = storage_path().'/tmp/'.$name.'.mp3';
    // 	$download = file_put_contents($downloadPath, $song);
    // 	if($download){
    // 		return $downloadPath;
    // 	}
    // 	else
    // 		return false;
    // }	

    public function checkID3($path){
    	include_once('../vendor/getid3/getid3.php');
    	$getID3 = new \getID3;
    	$info = $getID3->analyze($path);
    	if(array_key_exists('tags', $info)){
    		return 'set';
    	}
    	return $info;
    }

    public function setID3($path, $details){
    	include_once('../vendor/getid3/getid3.php');
    	$getID3 = new \getID3;
    	$TextEncoding = 'UTF-8';
    	$getID3->setOption(array('encoding'=>$TextEncoding));
		require_once('../vendor/getid3/write.php');
		// Initialize getID3 tag-writing module
		$tagwriter = new \getid3_writetags;
		$tagwriter->filename = $path;
		$tagwriter->tagformats = array('id3v2.3');

		// set various options (optional)
		$tagwriter->overwrite_tags    = true;  // if true will erase existing tag data and write only passed data; if false will merge passed data with existing tag data (experimental)
		$tagwriter->remove_other_tags = false;
		$tagwriter->tag_encoding = $TextEncoding;
		$tagwriter->remove_other_tags = true;

		// populate data array
		$TagData = array(
			'title'  => array($details['song_name']),
			'artist' => array($details['artiste']),
			'album'  => array($details['album']),
			'track_number' => array($details['track_number'])
		);
		$tagwriter->tag_data = $TagData;

		//Write Tags
		if ($tagwriter->WriteTags()) {
			return true;
		} 
		else {
			return false;
		}
    }
}