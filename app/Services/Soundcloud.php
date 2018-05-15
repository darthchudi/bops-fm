<?php

namespace App\Services;

use Carbon\Carbon;

use GuzzleHttp\Exception\GuzzleException;

use GuzzleHttp\Client as Guzzle;

class Soundcloud{
    public $page, $getID3, tagwriter;
    protected $clientID;

    public function __construct(){
        $this->clientID = env('SOUNDCLOUD_KEY');
        include_once('../getid3/getid3.php');
        require_once('../getid3/write.php');
        $this->getID3 = new \getID3;
        $this->tagwriter = new \getid3_writetags;
    }

	public function fetchLinks($soundcloudUrl){
        $apiUrl = "https://api.soundcloud.com/resolve.json?url=$soundcloudUrl&client_id=$this->clientID";
        $response = file_get_contents($apiUrl);
        $data = json_decode($response);

        if($data->kind==="track"){
            $details = array();
            $details['song_name'] = $data->title;
            $details['artiste'] = $data->user->username;
            $details['cover_art'] = $data->artwork_url;
            $details['album'] = $data->title.' — Single';
            $details['kind'] = 'song';
            $downloadLink = $data->stream_url."?client_id=$this->clientID";
            $details['link'] = $downloadLink;

            //Convert Cover Art to a more suitable Size
            $details['cover_art'] = str_replace('large', 't500x500', $details['cover_art']);

            $details = $this->sanitize($details);
            return $details;
        } elseif($data->kind==='playlist'){
            $count = 1;
            $details['artiste'] = $data->user->username;
            $details['cover_art'] = str_replace('large', 't500x500', $data->artwork_url);
            $details['album'] = $data->title;
            $details['kind'] = 'playlist';
            $details['tracklist'] = [];
            foreach($data->tracks as $song){
                $details['tracklist'][] = [
                    "name"=>$song->title,
                    "link"=>$song->stream_url."?client_id=$this->clientID",
                    "trackNumber"=>$count
                ];
                $count++;
            }

            $details = $this->sanitize($details, true, true);
            return $details;   
        }
    }

	public function serverDownload($songUrl, $details){
        $client = new Guzzle();
        $response = $client->get($songUrl);
        $responseBody = $response->getBody();

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

        $songFolder = $dateFolder.'/'.$details['artiste'].' - '.$details['album'];
        $name = $details['artiste'].' - '.$details['song_name'].'.mp3';
        $downloadPath = "$songFolder/$name";

        if(! is_dir($songFolder)){
            mkdir($songFolder, 0700);
        }

        if(file_exists($downloadPath)){
            return $downloadPath;
        }

        $coverArtFileName = $details['artiste'].' - '.$details['album'].'.jpg';
        $coverArtPath = "$songFolder/$coverArtFileName";
        if(! file_exists($coverArtPath)){
            $coverArt = file_get_contents($details['cover_art']);

            $downloadCoverArt = file_put_contents($coverArtPath, $coverArt);
        }

        $download = file_put_contents($downloadPath, $responseBody);
        if(!$download){
            return false;
        }
        else{
            return $downloadPath;
        }
    }

    public function sanitize($details, $isAlbum=null, $isBandcamp=null){
        $unwantedCharacters = ["<", ">", "/", "\\", "/", "?", "\"", "*", "|", ":", "<", ">"];

        if($isAlbum==true){
            $details['album'] = str_replace($unwantedCharacters, '', $details['album']);
            $details['artiste'] = str_replace($unwantedCharacters, '', $details['artiste']);

            if($isBandcamp==null){
                foreach ($details['tracklist'] as $index => $value) {
                    $details['tracklist'][$index] = str_replace($unwantedCharacters, '', $details['tracklist'][$index]);
                }
                return $details;    
            }

            if($isBandcamp==true){
                foreach ($details['tracklist'] as $index => $value) {
                    $details['tracklist'][$index]['name'] = str_replace($unwantedCharacters, '', $details['tracklist'][$index]['name']);
                }
                return $details;
            }

        }
        
        $details['album'] = str_replace($unwantedCharacters, '', $details['album']);
        $details['artiste'] = str_replace($unwantedCharacters, '', $details['artiste']);
        $details['song_name'] = str_replace($unwantedCharacters, '', $details['song_name']);
        return $details;
    }

    public function checkID3($path){
    	$info = $this->getID3->analyze($path);
    	if(array_key_exists('tags', $info)){
    		return 'set';
    	}
    	return $info;
    }

    public function setID3($path, $details){
        // Initialize getID3 tag-writing module
    	$TextEncoding = 'UTF-8';
    	$this->getID3->setOption(array('encoding'=>$TextEncoding));
		$this->tagwriter->filename = $path;
		$this->tagwriter->tagformats = array('id3v2.3');

		// set various options (optional)
		$this->tagwriter->overwrite_tags    = true;  // if true will erase existing tag data and write only passed data; if false will merge passed data with existing tag data (experimental)
		$this->tagwriter->remove_other_tags = false;
		$this->tagwriter->tag_encoding = $TextEncoding;
		$this->tagwriter->remove_other_tags = true;

		// populate data array
		$TagData = array(
			'title'  => array($details['song_name']),
			'artist' => array($details['artiste']),
			'album'  => array($details['album']),
            'band'=>array($details['artiste']),
			'track_number' => array($details['track_number'])
		);
		$this->tagwriter->tag_data = $TagData;

		//Write Tags
		if ($this->tagwriter->WriteTags()) {
			return true;
		} 
		else {
			return false;
		}
    }
}