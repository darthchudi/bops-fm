<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Bandcamp as Bandcamp;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use \File;
class BandcampController extends Controller
{
	protected $bandcamp;

	public function __construct(){
		$this->bandcamp = new Bandcamp();
	}

    public function getLinks(Request $request){
    	$url = $request->url;
    	$links = $this->bandcamp->getLinks($url);
    	return response()->json($links);
    }

    public function determineLink(Request $request){
    	if($this->bandcamp->isAlbum($request->url)){
    		return response()->json(['type'=>'album']);
    	}

    	if($this->bandcamp->isSong($request->url)){
    		return response()->json(['type'=>'song']);
    	}
    }


    public function download(Request $request){
    	$url = $request->url;

    	if($this->bandcamp->isSong($url)){
    		list($link) = $this->bandcamp->getLinks($url);
    		$details = $this->bandcamp->getSongDetails($url);
    		$download = $this->bandcamp->downloadSong($link, $details);
    		if($download){
    			echo 'Succesfully downloaded '.$details['song_name'];
    		}
    		else
    			echo 'heh';
    	}

    	if($this->bandcamp->isAlbum($url)){
    		$details = $this->bandcamp->getAlbumDetails($url);
    		dd($details);
    	}
    }

    public function getTracklist(Request $request){
    	$url = $request->url;
    	$details = $this->bandcamp->getAlbumDetails($url);
    	return response()->json($details);
    }

    public function getSongDetails(Request $request){
    	$url = $request->url;
    	$details = $this->bandcamp->getSongDetails($url);
    	$details['link']= $this->bandcamp->getLinks($url);
    	// $details['path'] = $this->downloadToServer($details['link'], $details);
    	return response()->json($details);
    }


    public function downloadToServer(Request $request){
    	$url = $request->url;
    	$title = $request->title;
    	$filename = $this->bandcamp->downloadToServer($url, $title); 	
    	return $filename;
    }

    public function fetchFile(Request $request){
    	$filename = $request->fileName;
    	$path = storage_path().'/tmp/'.$filename.'.mp3';
    	$type = File::mimeType($path);
    	$headers = ['Content-Type'=> $type, 'Content-Disposition'=> 'attachment; filename="'.$path.'"'];
    	return response()->download($path, $filename, $headers);
    }

    public function test(){
    	$path = storage_path().'/tmp/Sampa The Great - Healing.mp3';
    	$id3 = $this->bandcamp->checkID3($path);
    	if(array_key_exists('tags', $id3)){
    		dd($id3);
    	}
    	else {
    		//Retrieve code to fetch details
    		$response = $this->bandcamp->setID3($path);
    		dd($response);
    	}
    }


}