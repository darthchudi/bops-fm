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
        list($link) = $this->bandcamp->fetchLinks($url);
        $metaData = $this->bandcamp->fetchSongMetaData();
        $metaData['link'] = $link;
        return response()->json(["metaData"=>$metaData], 200);
    }

    public function downloadSingle(Request $request){
        $link = $request->link;
        $details = $request->details;
        $downloadedFile = $this->bandcamp->serverDownload($link, $details);
        if($downloadedFile){
            if($this->bandcamp->checkID3($downloadedFile)=='set'){
                return response()->json(["details"=>$details, "songPath"=>$downloadedFile, "message"=>"Nothing to set!"], 200);
            }

            $details['track_number'] = isset($details['track_number']) ? $details['track_number'] :  1;
            $setID3 =$this->bandcamp->setID3($downloadedFile, $details);

            if($setID3){
                return response()->json(["details"=>$details, "songPath"=>$downloadedFile, "message"=>"Downloaded and set ID3 tags!"], 200);
            } else{
                return response()->json(["details"=>$details, "songPath"=>$downloadedFile, "message"=>"Downloaded but could not set ID3 tags!"], 200);
            }
        }
        else{
            return response()->json("Error", 500);
        }
    }   

    public function fetchAlbumLinks(Request $request){
    	$url = $request->url;
    	$links = $this->bandcamp->fetchLinks($url);
        $metaData = $this->bandcamp->fetchAlbumDetails();
        $metaData['links'] = $links;
    	return response()->json([
            "metaData"=>$metaData
        ], 200);
    }

    public function serveDownload(Request $request){
        $filename = "MIKE - ANU";
        $path = $request->filePath;
        $type = File::mimeType($path);
        $headers = ['Content-Type'=> 'audio/mpeg', 'Content-Disposition'=> 'attachment; filename="'.$path.'"'];
        return response()->download($path, $filename, $headers);
    }


    public function download(Request $request){
    	$url = $request->url;

    	if($this->bandcamp->isSong($url)){
    		list($link) = $this->bandcamp->fetchLinks($url);
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

    public function fetchFile(){
    	$filename = "MIKE - ANU";
    	$path = "/home/vagrant/Code/bandcamp/storage/tmp/Apr 21, 2018/MIKE - TONIGHT, WITH YOU/MIKE - ANU.mp3";
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

    public function sandbox(){
        $song = "Gardens <ft. Tau Benah + Chris Generalz>";
        $album = " <insert project name​\​>";
    }



}