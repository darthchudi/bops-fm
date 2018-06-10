<?php
namespace App\Services;

class SoundCloudLikes{
    public $user, $likes;
    public function fetchLikes($profileUrl){
        $clientID = env('SOUNDCLOUD_KEY');
        $resolveUserUrl = "https://api.soundcloud.com/resolve.json?url=$profileUrl&client_id=$clientID";
        $resolveUserResponse = json_decode(file_get_contents($resolveUserUrl));
        $userID = $resolveUserResponse->id;
        $userMetadataUrl  = "https://api.soundcloud.com/users/$userID?client_id=$clientID";  
        $this->user = json_decode(file_get_contents($userMetadataUrl));
        $this->user->avatar_url = str_replace('large', 't500x500', $this->user->avatar_url);

        $userLikesUrl = "https://api.soundcloud.com/users/$userID/favorites?limit=50&linked_partitioning=10&client_id=$clientID";
        $response = json_decode(file_get_contents($userLikesUrl));
        $this->likes = $response->collection;
        $batches = '';
        if(property_exists($response, 'next_href')){
            $batches = $response->next_href; 

        }
        return [$this->user, $this->likes, $batches];
    }
}