<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Soundcloud as Soundcloud;

use App\Services\SoundcloudLikes as SoundcloudLikes;

use JonnyW\PhantomJs\Client as Client;

use Symfony\Component\DomCrawler\Crawler as Crawler;

use Goutte;

use GuzzleHttp\Exception\GuzzleException;

use GuzzleHttp\Client as Guzzle;

class SoundcloudController extends Controller
{
    protected $soundcloud;
    public function __construct(){
        $this->soundcloud = new Soundcloud();
    }

    public function fetchLinks(Request $request){
        $url = $request->url;
        $metaData = $this->soundcloud->fetchLinks($url);
        $metaData['service'] = 'soundcloud';
    	return response()->json($metaData, 200);
    }


    public function downloadSingle(Request $request){
        $link = $request->link;
        $details = $request->details;
        $downloadedFile = $this->soundcloud->serverDownload($link, $details);
        if($downloadedFile){
            if($this->soundcloud->checkID3($downloadedFile)=='set'){
                return response()->json(["details"=>$details, "songPath"=>$downloadedFile, "message"=>"Nothing to set!"], 200);
            }

            $details['track_number'] = isset($details['track_number']) ? $details['track_number'] :  1;
            $setID3 =$this->soundcloud->setID3($downloadedFile, $details);

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
     
    public function serveUserDownload(Request $request){
        $songPath = $request->songPath;
        return response()->download($songPath);
    }
    
    public function fetchLikes(Request $request){
        $profileUrl = $request->profileUrl;
        $soundcloudLikes = new SoundcloudLikes();
        list($user, $likes, $batches) = $soundcloudLikes->fetchLikes($profileUrl);
        return response()->json([
            "user"=>$user,
            "likes"=>$likes,
            "batches"=>$batches
        ], 200);
    }
}
