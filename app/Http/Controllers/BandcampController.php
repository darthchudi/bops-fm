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

    public function getPage(Request $request){
    	// $this->bandcamp->getPage($request->url);
    	if($this->bandcamp->isAlbum($request->url)){
    		echo 'Issa Album';
    	}

    	if($this->bandcamp->isSong($request->url)){
    		echo 'Issa Song';
    	}
    }

    public function isSong($songUrl){
    	
    }


    public function test(){
    	$html = $this->bandcamp->getPage('https://miloraps.bandcamp.com/album/who-told-you-to-think');
    	
    	$song = $this->bandcamp->downloadSong($html[0]);

    	$this->bandcamp->id('../downloads/eba.mp3');



    }
}