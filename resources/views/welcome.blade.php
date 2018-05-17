<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="csrf-token" content="{{csrf_token()}}">
<head>
    <title>Bops</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/css/font awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap/dist/css/bootstrap.min.css">
    <script type="text/javascript" src="/css/bootstrap/assets/js/vendor/jquery-slim.min.js"></script>
    <script type="text/javascript" src="/css/bootstrap/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/bops-fm.css">
    <link rel="stylesheet" type="text/css" href="/css/animate.css">
    <link rel="stylesheet" type="text/css" href="/css/wave-modal.css">
    <link rel="shortcut icon" href="/images/favicon.ico?v=2">
</head>
<body class="bg-gradient">
    <div id="root" v-cloak>
        <loading-modal :status="status" v-if="loading"> </loading-modal>
        <success-modal :status="successMessage" v-if="success"> </success-modal>
        <error-modal :status="errorMessage" v-if="error"> </error-modal>



        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <a href="#" class="navbar-brand text-dark">
                <i class="fa fa-music logo"></i> <span class="logo-tag ml-2"></span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle Navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav ml-auto">
                    <li class="navbar-item mr-3">
                        <a href="/" class="nav-link text-dark active">Home</a>
                    </li>

                    <li class="navbar-item mr-3">
                        <a href="/about" class="nav-link text-dark">About</a>
                    </li>

                    <li class="text-dark navbar-item">
                        <a href="/collab" class="nav-link text-dark">Collab</a>
                    </li>
                </ul>
            </div>  
        </nav>

        <main class="mt-5">
            <div class="container">
                <div class="row mb-5">
                    <div class="col">
                        <form @submit.prevent="submit">
                            <div class="form-row">
                                <div class="col-sm col-md-8 container">
                                    <div class="input-group ml-sm-0 ml-md-5">
                                        <div class="input-group-prepend rounded-0 bg-white">
                                            <span class="input-group-text bg-white border-white" id="icon">
                                                <i class="fa " :class="linkDetails[1]"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg border-0 rounded-0 is-valid" placeholder="Enter a bandcamp or soundcloud link" aria-label="link" aria-describedby="icon" v-model="link" name="url">
                                        <br/><small class="valid-feedback text-white ml-1 mb-3">It could be a song or album link. We don't mind :) </small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-sm col-md-8 container">
                                    <input type="submit" name="button" class="ml-sm-0 ml-md-5 container p-custom btn btn-outline-light rounded-0" value="Get bop(s)">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        

        <song-box v-if="fetchedSong" :song="songDetails"> </song-box>
        <album-box v-if="fetchedAlbum" :album-details="albumDetails" :tracklist="albumTracklist"> </album-box>


        <main class="bg-light pt-4 text-center " :class="[!fetchedSong && !fetchedAlbum ? 'fixed-bottom' : '']">
            <span>
                Made with 💛 + ☕ + Jollof by <span class="chudi"> Chudi. </span>
            </span> <br/>

            <span class="ml-2">
                <a href="https://twitter.com/chudioranu" class="mr-3">
                    <i class="fa fa-twitter text-primary"></i>
                </a>

                <a href="https://github.com/darthchudi">
                    <i class="fa fa-github text-dark"></i>
                </a>
            </span> <br/>
        </main>
    </div>
    

    
</body>
</html>

<script type="text/javascript" src="/js/welcome.js"></script>