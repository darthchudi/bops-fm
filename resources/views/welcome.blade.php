<!DOCTYPE html>
<html>
<head>
    <title>Bandcamp DL</title>
    <link rel="stylesheet" type="text/css" href="/css/welcome.css">
</head>
<body>
    <div class="form-box">
        <form method="POST" action="/download">
            {{csrf_field()}}
            <input type="text" name="url" placeholder="Enter Song URL">
            <br/>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>
    
</body>
</html>