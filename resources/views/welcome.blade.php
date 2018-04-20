<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="csrf-token" content="{{csrf_token()}}">
<head>
    <title>Bops FM</title>
    <link rel="stylesheet" type="text/css" href="/css/bulma-0.6.2/css/bulma.css">
    <link rel="stylesheet" type="text/css" href="/css/font awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/css/bandcamp.css">
    <link rel="stylesheet" type="text/css" href="/css/wave-modal.css">
    <link rel="shortcut icon" href="/images/favicon.ico">
</head>
<body>
    <div class="jumbotron jumbo" id="root">
        <img src="/images/bops.png" class="logo">
        <ul>
            <li> <a href="">About</a> </li>
            <li> <a href="">Mixes</a> </li>
            <li> <a href="">Discover</a> </li>
        </ul>
        <form @submit.prevent="submit">
            <div class="field">
                <div class="control has-icons-left">
                    <input type="text" name="url" placeholder="Enter a bandcamp/soundcloud song or album url..." class="input" v-model="link">
                    <span class="icon is-small is-left">
                        <i class="fa " :class="linkDetails[1]"></i>
                    </span>            
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <input type="submit" name="submit" value="Download the bops" class="submit button is-success is-medium">
                </div>
            </div>
        </form>

        <loading-modal :status="status" v-if="loading"> </loading-modal>
    </div>
    

    <footer class="footer">
        <div class="container">
            <div class="content has-text-centered">
              <p class="footer-text">
                    Made with 💛 and ☕ by  <a href="https://github.com/darthchudi" class="github"> Chudi. </a>
              </p>
            </div>
        </div>
    </footer>

    
</body>
</html>

<script type="text/javascript" src="/js/welcome.js"></script>