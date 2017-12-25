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
            $(".submit").click(function(e){
                //Prevent Page from reloading/Submitting
                e.preventDefault();                

                //Set variables to be used in AJAX request
                var _token = $("input[name='_token']").val();
                var url = $("input[name='url']").val();

               
                
                //Async function to fetch MetaData and Download Links
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

                //Call the async function 
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

                            $(".details ol")
                                .append("<li class='tracks'>" +song+"&nbsp;&nbsp;&nbsp; <a href='"+link+"'> Download </a>" + "</li>" );
                        }
                        console.log(data);
                    })
                    .catch((error) => {
                        $(".details").empty();
                        $(".details").append("<p> Sorry No results found </p>");
                        console.error(error);
                    });
            });
        });

    </script>
    
</body>
</html>