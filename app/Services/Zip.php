<?php
namespace App\Services;
use Carbon\Carbon;
use Illuminate\Http\File;
use Alchemy\Zippy\Zippy;

class Zip{
	public $albumDirectory, $coverArt, $getID3, $tagwriter;

	public function __construct(){
		include_once('../getid3/getid3.php');
    	require_once('../getid3/write.php');
    	$this->getID3 = new \getID3;
		$this->tagwriter = new \getid3_writetags;
	}

	public function createAlbumDirectory($details){
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

        $this->albumDirectory = $dateFolder.'/'.$details['artiste'].' - '.$details['album'];

        if(! is_dir($this->albumDirectory)){
            mkdir($this->albumDirectory, 0700);
        }

        return $this->albumDirectory;
	}

	public function downloadCoverArt($albumDetails){
		$coverArtFileName = $albumDetails['artiste'].' - '.$albumDetails['album'].'.jpg';
        $coverArtPath = "$this->albumDirectory/$coverArtFileName";
        if(! file_exists($coverArtPath)){
            $coverArt = file_get_contents($albumDetails['cover_art']);
            file_put_contents($coverArtPath, $coverArt);
        }

        return $coverArtPath;
	}

	public function createZipFile($albumDetails){
		$zippy = Zippy::load();
		$zipName = $albumDetails['artiste'].' - '.$albumDetails['album'].'.zip';
		$pathToZipFile = "$this->albumDirectory/$zipName";

		if(file_exists($pathToZipFile)){
			return $pathToZipFile;
		}

		$archive = $zippy->create($pathToZipFile, array(
			$this->albumDirectory
		), true);


		return $pathToZipFile;
	}

	public function downloadAllSongs($albumDetails, $tracklist){
		$errors = [];
		$downloadedSongs = array();
		$this->albumDirectory = $this->createAlbumDirectory($albumDetails);
		$this->coverArt = $this->downloadCoverArt($albumDetails);

		foreach ($tracklist as $song) {
    		$downloadedSong = $this->downloadSong($albumDetails, $song);
    		if($downloadedSong){
    			$downloadedSongs[] = $downloadedSong;
    		}
    		if(!$downloadedSong){
    			$error[] = "An error occured while downloading".$song['name'];
    		}
    	}

    	if(count($errors) > 0) {
    		return [
    			'data'=>$errors,
    			'status'=>500
    		];
    	}

    	$zipFile = $this->createZipFile($albumDetails);
    	return [
    		'data'=>$zipFile, 
    		'status'=>200
    	];
	}

	public function downloadSong($albumDetails, $song){
        $name = $albumDetails['artiste'].' - '.$song['name'].'.mp3';
        $downloadPath = "$this->albumDirectory/$name";

        if(file_exists($downloadPath)){
            return $downloadPath;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $song['link']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $song = curl_exec($ch);
        curl_close ($ch);

        //Fix issue with downloading soundcloud files
        if($albumDetails['service']==='soundcloud'){
	        $decodedSongData = json_decode($song);
	        $song = file_get_contents($decodedSongData->location);	
        }

        $download = file_put_contents($downloadPath, $song);
        
        if(!$download){
            return false;
        }
        else{
            return $downloadPath;
        }
	}

	public function checkAndSetID3($filePath){

	}
}