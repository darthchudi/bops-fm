<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Bandcamp as Bandcamp;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use \File;
use Carbon\Carbon;

class BandcampController extends Controller
{
	protected $bandcamp;

	public function __construct(){
		$this->bandcamp = new Bandcamp();
	}

    public function fetchLink(Request $request){
        $url = $request->url;
        list($link) = $this->bandcamp->getLinks($url);
        $metaData = $this->bandcamp->fetchSongMetaData();
        $metaData['link'] = $link;
        return response()->json(["metaData"=>$metaData], 200);
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
    		$details = $this->bandcamp->fetchSongMetaData();
    		$download = $this->bandcamp->serverDownload($link, $details);
    		if($download){
                return response()->json(["details"=>$details, "songPath"=>$download], 200);
    		}
    		else
    			return response()->json("Error", 500);
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
    	$type = $request->type;
    	$track_number = $request->track_number;

        $id3 = $this->bandcamp->checkID3($filePath);
        if($id3=='set'){
            return 'Nothing to do here';
        }
    	
    	if($type=='Album'){
    		$pageDetails = $this->bandcamp->getAlbumDetails($pageUrl);
    		$pageDetails['album'] = $pageDetails['album_name'];
    		$pageDetails['song_name'] = $pageDetails['tracklist'][$track_number-1];
    	}

    	if($type=='Song'){
    		$pageDetails = $this->bandcamp->getSongDetails($pageUrl);
    	}
    	
    	$pageDetails['track_number'] = $track_number;
		$this->bandcamp->setID3($filePath, $pageDetails);
		return 'Set ID3 tags!'; 
    }

    public function test(){
    	$path = storage_path().'/tmp/MIKE - ANU.mp3';
    	$type = File::mimeType($path);
    	$id3 = $this->bandcamp->checkID3($path);
    	dd($id3);
    }

    public function sandbox(){
        $song = "Gardens <ft. Tau Benah + Chris Generalz>";
        $album = " <insert project name​\​>";
    }



}