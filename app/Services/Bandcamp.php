<?php

namespace App\Services;
use JonnyW\PhantomJs\Client as Client;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use Carbon\Carbon;

class Bandcamp{
    public $page;

	public function fetchLinks($url){
		$this->page = file_get_contents($url);
	  	preg_match_all('/"mp3-128":"(.*?)"/', $this->page, $array);
		return $array[1];
	}

	public function fetchAlbumDetails(){
    	$crawler = new Crawler($this->page);
    	$title = $crawler->filterXpath('//title')->text();
    	list($details['album']) = explode('|', $title);
    	$details['artiste'] = $crawler->filterXpath('//span[@itemprop="byArtist"]//a')->text();
    	$details['tracklist'] = $crawler->filterXpath('//span[@itemprop="name"]')->each(function(Crawler $node, $i){
    		return $node->text();
    	});

    	$details['cover_art']= $crawler->filterXpath('//div[@id="tralbumArt"]//a/@href')->text();

        $details = $this->sanitize($details, true);
    	
    	return $details;
    }

    public function fetchSongMetaData(){
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


        $details = $this->sanitize($details);

    	return $details;
    }

	public function serverDownload($songUrl, $details){
        $dateFolder = storage_path().'/tmp/General/';
        $presentDate = Carbon::now()->toFormattedDateString();
        if(is_dir(storage_path().'/tmp/'.$presentDate)){
            $dateFolder = storage_path()."/tmp/$presentDate";
        } 
        else{
            $createdDirectory = mkdir(storage_path()."/tmp/$presentDate", 0700);
            if($createdDirectory){
                $dateFolder = storage_path()."/tmp/$presentDate";
            }
        }

        $songFolder = $dateFolder.'/'.$details['artiste'].' - '.$details['album'];
        $name = $details['artiste'].' - '.$details['song_name'].'.mp3';
        $downloadPath = "$songFolder/$name";

        if(! is_dir($songFolder)){
            mkdir($songFolder, 0700);
        }

        if(file_exists($downloadPath)){
            return $downloadPath;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec($ch);
        curl_close ($ch);

        $coverArtFileName = $details['artiste'].' - '.$details['album'].'.jpg';
        $coverArtPath = "$songFolder/$coverArtFileName";
        if(! file_exists($coverArtPath)){
            $coverArt = file_get_contents($details['cover_art']);

            $downloadCoverArt = file_put_contents($coverArtPath, $coverArt);
        }

        $download = file_put_contents($downloadPath, $song);
        
        if(!$download){
            return false;
        }
        else
            return $downloadPath;
    }

    public function sanitize($details, $isAlbum=null){
        $unwantedCharacters = ["<", ">", "/", "\\", "/", "?", "\"", "*", "|", ":", "<", ">"];

        if($isAlbum==true){
            $details['album'] = str_replace($unwantedCharacters, '', $details['album']);
            $details['artiste'] = str_replace($unwantedCharacters, '', $details['artiste']);
            foreach ($details['tracklist'] as $index => $value) {
                $details['tracklist'][$index] = str_replace($unwantedCharacters, '', $details['tracklist'][$index]);
            }
            return $details;
        }
        
        $details['album'] = str_replace($unwantedCharacters, '', $details['album']);
        $details['artiste'] = str_replace($unwantedCharacters, '', $details['artiste']);
        $details['song_name'] = str_replace($unwantedCharacters, '', $details['song_name']);
        return $details;
    }

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