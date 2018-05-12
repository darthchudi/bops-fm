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
        $metaData['service'] = 'bandcamp';
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
            $setID3 = $this->bandcamp->setID3($downloadedFile, $details);

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
        $metaData['service'] = 'bandcamp';
    	return response()->json([
            "metaData"=>$metaData
        ], 200);
    }

    public function serveUserDownload(Request $request){
    	$songPath = $request->songPath;
        return response()->download($songPath)->deleteFileAfterSend(true);
    }



}