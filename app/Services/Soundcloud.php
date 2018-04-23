<?php

namespace App\Services;

use Carbon\Carbon;

use GuzzleHttp\Exception\GuzzleException;

use GuzzleHttp\Client as Guzzle;

class Soundcloud{
    public $page;
    protected $clientID = '22e8f71d7ca75e156d6b2f0e0a5172b3';

	public function fetchLinks($soundcloudUrl){
        $apiUrl = "http://api.soundcloud.com/resolve?url=$soundcloudUrl&client_id=$this->clientID";
        $response = file_get_contents($apiUrl);
        $data = json_decode($response);

        if($data->kind==="track"){
            $details = array();
            $details['song_name'] = $data->title;
            $details['artiste'] = $data->user->username;
            $details['cover_art'] = $data->artwork_url;
            $details['album'] = $data->title.' — Single';
            $downloadLink = $data->stream_url."?client_id=$this->clientID";
            // $downloadLink = file_get_contents($downloadLink);
            $details['link'] = $downloadLink;

            //Convert Cover Art to a more suitable Size
            $details['cover_art'] = str_replace('large', 't500x500', $details['cover_art']);

            $details = $this->sanitize($details);
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

        $download = file_put_contents($downloadPath, $responseBody);
        if(!$download){
            return false;
        }
        else{
            return $downloadPath;
        }
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