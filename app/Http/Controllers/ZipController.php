<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\Zip;

class ZipController extends Controller
{
    public function makeZip(Request $request){
    	$zip = new Zip();
    	$albumDetails = $request->albumDetails;
    	$tracklist = $request->tracklist;
    	$downloadAllSongs = $zip->downloadAllSongs($albumDetails, $tracklist);

    	if($downloadAllSongs['status']==500){
    		return response()->json($downloadAllSongs['data'], 500);
    	}

    	return response()->json($downloadAllSongs['data'], 200);
    }

    public function downloadZip(Request $request){
    	$zipFilePath = $request->zipFilePath;
    	return response()->download($zipFilePath);
    }
}
