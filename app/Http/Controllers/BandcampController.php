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
    	$downloadLinks = $this->bandcamp->getLinks($request->url);
    	$tracklist = $this->bandcamp->getDetails($request->url);

    	$this->bandcamp->downloadSong($downloadLinks[3]);
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
    		// $links = $this->bandcamp->getLinks($url);
    		$details = $this->bandcamp->getSongDetails($url);
    		dd($details);
    	}

    	if($this->bandcamp->isAlbum($url)){
    		$details = $this->bandcamp->getAlbumDetails($url);
    		dd($details);
    	}




    }
}