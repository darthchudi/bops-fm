<?php

namespace App\Services;
use JonnyW\PhantomJs\Client as Client;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use Carbon\Carbon;
use Storage;
use Illuminate\Http\File;

class Bandcamp{
    public $page;
    public $getID3, $tagwriter;

    public function __construct(){
    	include_once('../getid3/getid3.php');
    	require_once('../getid3/write.php');
    	$this->getID3 = new \getID3;
		$this->tagwriter = new \getid3_writetags;
    }

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
    	$details['song_name'] = trim($details['song_name']);

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


    /* ---------------------------------------------- S3 Stuff ---------------------------------------------------- */

    public function s3Download($songUrl, $details){
    	//Use Carbon to generate folder name of the current date
    	$presentDateFolder = Carbon::now()->toFormattedDateString();

    	//Create an S3 Directory with the present data only if the directory isn't present. If the process fails, return false.
    	$baseDirectories = Storage::cloud()->directories();
    	if(! in_array($presentDateFolder, $baseDirectories)){
    		$didDateFolderCreate = Storage::cloud()->makeDirectory($presentDateFolder);
    		if(! $didDateFolderCreate){
    			return false;
    		}
    	}

    	//Generate Folder and song names
        $songFolder = $presentDateFolder.'/'.$details['artiste'].' - '.$details['album'];
        $name = $details['artiste'].' - '.$details['song_name'].'.mp3';
        $downloadPath = "$songFolder/$name";

        //Check if the song folder exists, if it doesnt create one
        $presentDateDirectories = Storage::cloud()->directories($presentDateFolder);
        if(! in_array($songFolder, $presentDateDirectories)){
        	$didSongFolderCreate = Storage::cloud()->makeDirectory($songFolder);
        	if(! $didSongFolderCreate){
        		return false;
        	}
        }

        //Check if the song itself has been downloaded before. If it has return
       	if(Storage::cloud()->exists($downloadPath)){
       		$response = [
				"downloadPath"=>$downloadPath,
				"songFolder"=>$songFolder
			];
            return $response;
       	}
        	
        //If we get to this point i.e file hasn't been downloaded, we initalize the download
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $songUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $songDataFromCurl = curl_exec($ch);
        curl_close ($ch);

        //Generate cover art file name and save
        $coverArtFileName = $details['artiste'].' - '.$details['album'].'.jpg';
        $coverArtPath = "$songFolder/$coverArtFileName";

        //Check if we've downloaded cover art already, if we haven't download it.
        if(! Storage::cloud()->exists($coverArtPath)){
            $coverArt = file_get_contents($details['cover_art']);
            Storage::cloud()->put($coverArtPath, $coverArt);
        }

        //Decode JSON string response into PHP object and get content [Only if it is a soundcloud song]
        if(strpos($songUrl, 'soundcloud')){
	        $decodedSongData = json_decode($songDataFromCurl);
	        $songDataFromCurl = file_get_contents($decodedSongData->location);	
        }

        //Finally, we download the song itself
        $download = Storage::cloud()->put($downloadPath, $songDataFromCurl);

        //Return the song path
        if(!$download){
            return false;
        }
        else{
        	$response = [
				"downloadPath"=>$downloadPath,
				"songFolder"=>$songFolder
			];
            return $response;
        }
    }

    public function s3CheckID3($s3FileUrl, $details, $songFolder){
    	// Copy remote file locally to scan with getID3()
		if ($fp_remote = fopen($s3FileUrl, 'rb')) {
		    $localtempfilename = tempnam('/tmp', 'getID3');
		    if ($fp_local = fopen($localtempfilename, 'wb')) {
		        while ($buffer = fread($fp_remote, 8192)) {
		            fwrite($fp_local, $buffer);
		        }
		        fclose($fp_local);

		        $info = $this->getID3->analyze($localtempfilename);
		    }
		    fclose($fp_remote);
		}


		if(! array_key_exists('tags', $info)){
			$s3SetID3 = $this->s3SetID3($localtempfilename, $details, $songFolder);
    		return $localtempfilename;
    	}

    	return $localtempfilename;
	}

	public function s3SetID3($localtempfilename, $details, $songFolder){
		$presentDateFolder = Carbon::now()->toFormattedDateString();

		//Set the text encoding and various setups required to write tags
    	$TextEncoding = 'UTF-8';
    	$this->getID3->setOption(array('encoding'=>$TextEncoding));
		$this->tagwriter->filename = $localtempfilename;
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
			"band"=> array($details['artiste']),
			'track_number' => array($details['track_number'])
		);

		// $cover_art = file_get_contents($details['cover_art']);
		// $TagData['attached_picture'][0]['data'] = $cover_art;
		// $TagData['attached_picture'][0]['picturetypeid'] = 3;
		// $TagData['attached_picture'][0]['description'] = 'Cover';
		// $TagData['attached_picture'][0]['mime']  = 'image/jpeg';

		$this->tagwriter->tag_data = $TagData;

		//Write Tags
		if ($this->tagwriter->WriteTags()) {
			$fileName = $details['artiste'].' - '.$details['song_name'].'.mp3';
			$response = Storage::cloud()->putFileAs($songFolder, new File($localtempfilename), $fileName);
			return true;
		} 
		else {
			return false;
		}
	}


}