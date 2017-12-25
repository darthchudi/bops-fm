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
                
                //Async function to fetch MetaData
                async function doAjax(){
                    let result;
                    result = await $.ajax({
                        type: 'POST',
                        url: "getTracklist",
                        data: {url: url, _token: _token},
                    });
                    return result; 
                }

                //Call the async function 
                doAjax()
                    .then( (data) => {
                        $(".details").empty();
                        $(".details").append("<h3> Artiste: "+data.artiste + " </h3>");
                        $(".details").append("<h3> Album: "+data.album_name + " </h3>");
                        $(".details").append("<ol class='tracklist'> </ol>");
                        data.tracklist.forEach(function(song, key){
                            var track_number = key+1;
                            $(".details ol").append("<li class='tracks'>" +song+"</li>" );
                        });
                        console.log(data);
                    })
                    .catch((error) => {
                        $(".details").empty();
                        $(".details").append("<p> Sorry No results found </p>");
                        console.error(error);
                    });





                // $.ajax({
                //     type: 'POST',
                //     url: "getTracklist",
                //     data: {url: url, _token: _token},
                //     success: function(data){
                //        $(".details").empty();
                //        $(".details").append("<h3> Artiste: "+data.artiste + " </h3>");
                //        $(".details").append("<h3> Album: "+data.album_name + " </h3>");
                //        $(".details").append("<ol class='tracklist'> </ol>");
                //        data.tracklist.forEach(function(song, key){
                //             var track_number = key+1;
                //             $(".details ol").append("<li class='tracks'>" +song+"</li>" );
                //        });
                //        console.log(data);
                //        tracklist = data.tracklist;
                //     }
                // });
                
            });
        });

    </script>
    
</body>
</html>