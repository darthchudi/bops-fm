<!DOCTYPE html>
<html>
<head>
    <title>Bandcamp DL</title>
    <link rel="stylesheet" type="text/css" href="/css/welcome.css">
    <script type="text/javascript" src="/js/jquery-3.2.1.js"></script>
</head>
<body>
    <div class="form-box">
        <form>
            {{csrf_field()}}
            <input type="text" name="url" placeholder="Enter Song URL">
            <br/>
            <input type="submit" name="submit" value="Submit" class="submit">
        </form>
    </div>

    <div class="details">
        
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            var exists;
            $(".submit").click(function(e){
                //Prevent Page from reloading/Submitting
                e.preventDefault();                

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

                //Call the async function to determine the link type
                linkType()
                    .then((type)=>{
                        //Call async function for handling albums
                        if(type.type=='album'){ 
                            fetchMetaData()
                                .then( (data) => {
                                    $(".details").empty();
                                    $(".details").append("<img src='"+data[0].cover_art+"' style='height: 350px; width: 350px'>");
                                    $(".details").append("<h3> Artiste: "+data[0].artiste + " </h3>");
                                    $(".details").append("<h3> Album: "+data[0].album + " </h3>");
                                    $(".details").append("<ol class='tracklist'> </ol>");

                                    for(var i=1; i<=data.length -1; i++){
                                        var song = data[i].song;
                                        var link = data[i].download_link;
                                        var track_number = data[i].track_number;
                                        var title = data[0].artiste +' - '+data[i].song;

                                        $(".details ol")
                                            .append('<li class="tracks">'+song+'&nbsp;&nbsp;&nbsp; <a href="'+link +'" class="download" id="'+title+'"> Download </a></li>');
                                    }
                                    console.log(data);
                                })
                                .catch((error) => {
                                    //Handle Errors when searching for album details
                                    $(".details").empty();
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
                                    console.log(data);
                                    var title = data.artiste + ' - '+data.song_name;
                                    $(".details").append("<img src='"+data.cover_art+"' style='height: 350px; width: 350px'>");
                                    $(".details").append("<h3> Song Name: "+data.song_name +"</h3>");
                                    $(".details").append('<a href="'+data.link +'"class="download" id="'+title+'"> Download </a>')
                                    $(".details").append("<h3> Artiste: "+data.artiste + " </h3>");
                                    $(".details").append("<h3> Album: "+data.album + " </h3>");       
                                })
                                .catch( (e)=>{
                                    //Handle Errors when fetching song details
                                    $(".details").empty();
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
            $(document).on('click', '.download', function(e){
                e.preventDefault();

                //Async function to download the song to our server from Bandcamp
                async function downloadToServer(url, title){
                    let path = await $.ajax({
                        type: 'GET',
                        url: '/downloadToServer',
                        data: {url: url, title: title}
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

                //Get the file title, download url and page url to attach metadata to mp3
                var title = $("a.download").attr('id');
                var url = $("a.download").attr('href');
                var pageUrl = $("input[name='url']").val();
                console.log(title);
                console.log(url);
                console.log(pageUrl);

                //Call async function to download the .mp3 file to our server
                downloadToServer(url, title)
                    .then((path)=>{
                        console.log(path);
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
                        //Handle error if we can't download file
                        $(".details").empty();
                        $(".details").append("<p> Oops! Couldn't download to server </p>");
                        console.error(e);
                    });
            });

        });
    </script>
    
</body>
</html>