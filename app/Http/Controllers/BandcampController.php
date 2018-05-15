<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Bandcamp as Bandcamp;
use Symfony\Component\DomCrawler\Crawler as Crawler;
use \File;
use Carbon\Carbon;
use Storage;

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
        $songTitle = $request->songTitle;
        return response()->download($songPath, $songTitle);
    }

    public function s3Download(Request $request){
        $link = $request->link;
        $details = $request->details;

        $downloadedFile = $this->bandcamp->s3Download($link, $details);

        if($downloadedFile){
            $downloadPath = $downloadedFile["downloadPath"];
            $songFolder = $downloadedFile["songFolder"];

            $s3FileUrl = Storage::cloud()->url($downloadPath);

            $details['track_number'] = isset($details['track_number']) ? $details['track_number'] :  1;

            $s3GetMeta = $this->bandcamp->s3CheckID3($s3FileUrl, $details, $songFolder);


            if($s3GetMeta){
                return response()->json(["details"=>$details, "songPath"=>$s3GetMeta, "message"=>"Downloaded and set ID3 tags!"], 200);
            } else{
                return response()->json(["details"=>$details, "songPath"=>$s3GetMeta, "message"=>"Downloaded but could not set ID3 tags!"], 200);
            }
        }
        else{
            return response()->json("Could not download file sir", 500);
        }
    } 

    public function s3Test(){
        $link = "https://t4.bcbits.com/stream/db8eb49bbd1154c022ebc2d1a78396dd/mp3-128/2010940830?p=0&ts=1526429941&t=0fc9f5f1519b964580a3c02d00b2015ce5dbad1b&token=1526429941_f78347b65b299c748d6a5ea55633d8ee6fa8df96";
        $details = [

            "artiste"=>"Sango",
            "album"=>"De Mim, Pra Você",
            "song_name"=>"Vista da Gávea ",
            "cover_art"=>"https://i1.sndcdn.com/artworks-000334532784-y5k0r9-t500x500.jpg",
            "track_number"=>10
        ];

        $downloadedFile = $this->bandcamp->s3Download($link, $details);
        $downloadPath = $downloadedFile["downloadPath"];
        $songFolder = $downloadedFile["songFolder"];
        $s3FileUrl = Storage::cloud()->url($downloadPath);
        echo $s3FileUrl;

        $s3GetMeta = $this->bandcamp->s3CheckID3($s3FileUrl, $details, $songFolder);

        echo $s3GetMeta ? 'All is well' : 'Error dey somewhere';  
    }
}