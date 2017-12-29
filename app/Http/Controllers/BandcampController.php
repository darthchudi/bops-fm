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
    	$filePath = $this->bandcamp->downloadToServer($url, $title); 	
    	return $title;
    }

    public function fetchFile(Request $request){
    	$filename = $request->fileName;
    	$path = storage_path().'/tmp/'.$filename.'.mp3';
    	$type = File::mimeType($path);
    	$headers = ['Content-Type'=> 'audio/mpeg', 'Content-Disposition'=> 'attachment; filename="'.$path.'"'];
    	return response()->download($path, $filename, $headers);
    }

    public function checkid3(Request $request){
    	$pageUrl = $request->pageUrl;
    	$filePath = storage_path().'/tmp/'.$request->filePath.'.mp3';
    	$pageDetails = $this->bandcamp->getSongDetails($pageUrl);
   
		$id3 = $this->bandcamp->checkID3($filePath);
   		if($id3=='set'){
   			return 'Nothing to do here';
   		}
   		else{
   			$this->bandcamp->setID3($filePath, $pageDetails);
   			return 'Set ID3 tags!'; 
   		}
    }

    public function test(){
    	$path = storage_path().'/tmp/Garvie - peridot.mp3';
    	$type = File::mimeType($path);
    	$id3 = $this->bandcamp->checkID3($path);
    	dd($type);
    }


}