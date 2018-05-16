<!DOCTYPE html>
<html>
<head>
	<title>Bops | About</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
 	<link rel="stylesheet" type="text/css" href="css/font awesome/css/font-awesome.css">
 	<link rel="stylesheet" type="text/css" href="css/bootstrap/dist/css/bootstrap.min.css">
	<script type="text/javascript" src="css/bootstrap/assets/js/vendor/jquery-slim.min.js"></script>
	<script type="text/javascript" src="css/bootstrap/dist/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/bops-fm.css">
</head>
<body class="bg-about img-fluid about">
	<nav class="navbar navbar-expand-md navbar-light bg-white">
		<a href="#" class="navbar-brand text-dark">
			<i class="fa fa-music logo"></i> <span class="logo-tag ml-2"> </span>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle Navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarCollapse">
			<ul class="navbar-nav ml-auto">
				<li class="navbar-item mr-3">
					<a href="/" class="nav-link text-dark">Home</a>
				</li>

				<li class="navbar-item mr-3">
					<a href="" class="nav-link text-dark active">About</a>
				</li>

				<li class="text-dark navbar-item">
					<a href="/collab" class="nav-link text-dark">Collab</a>
				</li>
			</ul>
		</div>	
	</nav>

	<main class="mt-4 mb-5">
		<div class="container">
			<h1 class="helvetica-n text-center text-white display-3 about-title">about.</h1>
			<p class="lead text-center text-white mt-4 about">
				Bops fm is an easy to use Soundcloud and Bandcamp downloader. All you need is a valid song or album Soundcloud/Bandcamp link. <br/>
				<span class="ps"> side note</span>: pls let your faves finnesse those plays before you decide to rip their music :)
			</p>
		</div>
	</main>

	@component('footer')
	@endcomponent
</body>
</html>