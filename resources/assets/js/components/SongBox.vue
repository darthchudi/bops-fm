<template>
	 <footer class="footer song-details">
        <div class="container">
            <div class="content has-text-centered">
                <h3><strong>{{song.song_name}} — Single</strong> by <strong> {{song.artiste}} </strong></h3>
                <div class="columns">
                    <div class="column is-8">
                        <img :src="song.cover_art" class="album-image">
                    </div>

                    <div class="column is-4">
                        <p class="footer-text">
                            <ul class="song-list">
                                <li class="song">💿 &nbsp; {{song.song_name}}
                                    <a :href="song.link"> 
                                        <span class="icon has-text-info">
                                            <i class="fa fa-cloud-download"></i>
                                        </span> 
                                    </a> 
                                </li>
                            </ul>
                            
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</template>


<script type="text/javascript">
	export default{
		created(){
			console.log(this.song);
		},
		props: ["song"]
	}
</script>