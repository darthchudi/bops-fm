<!DOCTYPE html>
<html>
<head>
    <title>Bandcamp DL</title>
    <link rel="stylesheet" type="text/css" href="/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/css/bandcamp.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.loadingModal.css">
    <script type="text/javascript" src="/js/jquery-3.2.1.js"></script>
</head>
<body>
    <div class="jumbotron jumbo">
        <form id="bandcamp">
            {{csrf_field()}}
            <input type="text" name="url" placeholder="Enter a bandcamp song or album url...">
            <br/>
            <input type="submit" name="submit" value="Fetch links! ➔" class="submit">
        </form>

        {{-- Div class for loading Screen--}}
        <div class="spinner load">
          <div class="rect1"></div>
          <div class="rect2"></div>
          <div class="rect3"></div>
          <div class="rect4"></div>
          <div class="rect5"></div>
        </div>
        <p class="load">please wait while we fetch download links...</p>

    </div>

    <div class="wrapper">
        <div class="details">
        
        </div>

    </div>


    <div class="zip">
        

    </div>
    <script type="text/javascript" src="/js/jquery.loadingModal.js"></script>
    <script type="text/javascript" src="/js/bandcamp.js"></script>
    
</body>
</html>