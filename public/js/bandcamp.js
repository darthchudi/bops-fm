$(document).ready(function(){
    var exists;
    $(".load").hide();
    $("#bandcamp").submit(function(e){
        //Prevent Page from reloading/Submitting
        e.preventDefault();  
        $(".load").show();              

        //Set variables to be used in AJAX request
        var _token = $("input[name='_token']").val();
        var url = $("input[name='url']").val();
        let type;

        //Async function to determine if link is an album or song
        async function linkType(){
            type = await $.ajax({
                type: 'POST',
                url: '/determineLink',
                data: {url: url, _token: _token}
            });
            return type;
        }               
        
        //Async function to fetch MetaData and Download Links for albums
        async function fetchMetaData(){
            let tracklist, metadata, links;
            tracklist = new Array();

            metadata = await $.ajax({
                type: 'POST',
                url: "getTracklist",
                data: {url: url, _token: _token},
            });

            links = await $.ajax({
                type: 'POST',
                url: 'getLinks',
                data: {url: url, _token:_token}
            });

            tracklist[0] = {
                'artiste': metadata.artiste,
                'album': metadata.album_name,
                'cover_art': metadata.cover_art
            };

            for(var i =1; i<=links.length; i++){
                tracklist[i] = {
                    'song': metadata.tracklist[i-1],
                    'download_link': links[i-1],
                    'track_number': i
                };
            }
            return tracklist; 
        }

        //Async function to fetch metadata and download link for Song
        async function getSongDetails(){
            let songDetails = await $.ajax({
                type: 'POST', 
                url: 'getSongDetails',
                data: {url:url, _token: _token}
            });
            return songDetails;
        }

        //Async function generate the zip file for albums
        async function makeZip($links, $details){
            
        }

        //Call the async function to determine the link type
        linkType()
            .then((type)=>{
                //Call async function for handling albums
                if(type.type=='album'){ 
                    fetchMetaData()
                        .then( (data) => {
                            $(".details").empty();
                            $(".zip").empty();
                            $(".load").hide();
                            $(".details").append("<img src='"+data[0].cover_art+"' style='height: 350px; width: 350px'>");
                            $(".details").append("<h3> Artiste: "+data[0].artiste + " </h3>");
                            $(".details").append("<h3> Album: "+data[0].album + " </h3>");
                            $(".details").append("<ol class='tracklist'> </ol>");

                            for(var i=1; i<=data.length -1; i++){
                                var song = data[i].song;
                                var link = data[i].download_link;
                                var track_number = data[i].track_number+'/Album';
                                var title = data[0].artiste +' - '+data[i].song;

                                $(".details ol")
                                    .append('<li class="tracks">'+song+'&nbsp;&nbsp;&nbsp; <a href="'+link +'" class="'+track_number+'" id="'+title+'"> Download </a></li>');
                            }

                            $(".zip").append("<br/><h3> To download the entire project as a zip file <a href='/makeZip'>Click me! </a></h3>");

                            console.log(data);
                        })
                        .catch((error) => {
                            //Handle Errors when searching for album details
                            $(".details").empty();
                            $(".zip").empty();
                            $(".load").hide();
                            $(".details").append("<p> Sorry No results found </p>");
                            console.error(error);
                        });
                }

                //Call async function for handling songs
                if(type.type=='song'){                            
                    getSongDetails()
                        .then( (data)=>{
                            //Begin working on displaying results    
                            $(".details").empty();
                            $(".zip").empty();
                            $(".load").hide();
                            console.log(data);
                            var title = data.artiste + ' - '+data.song_name;
                            var track_number = '1/Song';
                            $(".details").append("<img src='"+data.cover_art+"' style='height: 350px; width: 350px'>");
                            $(".details").append("<h3> Song Name: "+data.song_name +"</h3>");
                            $(".details").append('<a href="'+data.link +'"class="' + track_number +'" id="'+title+'"> Download </a>')
                            $(".details").append("<h3> Artiste: "+data.artiste + " </h3>");
                            $(".details").append("<h3> Album: "+data.album + " </h3>");       
                        })
                        .catch( (error)=>{
                            //Handle Errors when fetching song details
                            $(".details").empty();
                            $(".zip").empty();
                            $(".load").hide();
                            $(".details").append("<p> Sorry No results found </p>");
                            console.error(error);
                        });
                }

                //if a link is neither an album or song do the following
                if(type.type!='song' && type.type!='album'){
                    $(".details").empty();
                    $(".details").append("<p> Not a valid link! </p>");
                    console.error(e);
                }
                console.log(type);
            })

            .catch( (e) =>{
                //Handle Error if it can't evaluate the link
                $(".details").empty();
                $(".details").append("<p> Not a valid link! </p>");
                console.error(e);
            });
    });

    //If user left clicks on download button the call the function to download the file to the server and render it to the user for download
    $(document).on('click', 'a', function(e){
        e.preventDefault();

        //Async function to download the song to our server from Bandcamp
        async function downloadToServer(url, title, pageUrl){
            let path = await $.ajax({
                type: 'GET',
                url: '/downloadToServer',
                data: {url: url, title: title, pageUrl: pageUrl}
            });
            return path;
        }

        //Async function to retrieve the specific file from the server
        async function fetchFile(fileName){
            let file = await $.ajax({
                type: 'GET',
                url: '/fetchFile',
                data: {fileName: fileName}
            });
            return file;
        }

        //Async function to check and set the ID3 tag of the song
        async function checkID3(filePath, pageUrl, track_number, type){
            let id3 = await $.ajax({
                type: 'GET',
                url: '/checkid3',
                data: {filePath: filePath, pageUrl: pageUrl, track_number: track_number, type:type}
            });
            return id3;
        }

        //Get the file title, download url and page url to attach metadata to mp3
        var title = $(this).attr('id');
        var url = $(this).attr('href');
        var pageUrl = $("input[name='url']").val();
        var classDetails = $(this).attr('class').split('/');
        var track_number = classDetails[0];
        var type = classDetails[1];
        console.log(title);
        console.log(url);
        console.log(pageUrl);
        console.log(track_number);
        console.log(type);

        //Call async function to download the .mp3 file to our server
        downloadToServer(url, title, pageUrl)
            .then((path)=>{
                console.log(path);
                //After downloading the file, evaluate the id3 of the file
                checkID3(path, pageUrl, track_number, type)
                    .then((id3)=>{
                        console.log(id3);
                        //Call asyn function to fetch downloaded file from our server
                        fetchFile(path)
                            .then((success)=>{
                                console.log("Downloaded!");
                            })
                            .catch((e)=>{
                                //Handle Error if we can't downlod the file
                                $(".details").empty();
                                $(".details").append("<p> Oops! Couldn't fetch file from server</p>");
                                console.error(e);
                            });
                    })
                    .catch((e)=>{
                        //Handle errors that might occur while checking id3
                        $(".details").empty();
                        $(".details").append("<p> Oops! Couldn't evaluate metadata</p>");
                        console.error(e);
                    });
            })  
            .catch((e)=>{
                //Handle error if we can't download file
                $(".details").empty();
                $(".details").append("<p> Oops! Couldn't download to server </p>");
                console.error(e);
            });
    });

});
