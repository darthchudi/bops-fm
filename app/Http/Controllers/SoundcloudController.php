<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Soundcloud as Soundcloud;

use JonnyW\PhantomJs\Client as Client;

use Symfony\Component\DomCrawler\Crawler as Crawler;

use Goutte;

class SoundcloudController extends Controller
{
    protected $soundcloud;
    public function __construct(){
    	$this->soundcloud = new Soundcloud();
    }

    public function fetchLinks(Request $request){
    	$url = $request->url;
    	$details = $this->soundcloud->fetchLinks($url);
    	return response()->json($details, 200);
    }

    public function demo(){
        $clientID = '22e8f71d7ca75e156d6b2f0e0a5172b3';
        $url = "http://api.soundcloud.com/resolve?url=https://soundcloud.com/ozzybsounds/santi-icy-feat-izzy-maison2500-odunsi&client_id=$clientID";
        $response = file_get_contents($url);
        $obj = json_decode($response, true);
        $obj['artwork_url'] = str_replace('large', 't500x500', $obj['artwork_url']);
        return $obj;
    }
    	
}
