<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Bandcamp as Bandcamp;
use Symfony\Component\DomCrawler\Crawler as Crawler;
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

    public function determineLink($songUrl){
    	if($this->bandcamp->isAlbum($request->url)){
    		echo 'Issa Album';
    	}

    	if($this->bandcamp->isSong($request->url)){
    		echo 'Issa Song';
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


    public function test(Request $request){
    	$url = $request->url;
    	$details = $this->bandcamp->getAlbumDetails($url);
    	dd($details);
    }

}