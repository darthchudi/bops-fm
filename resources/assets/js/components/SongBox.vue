<template>
	 <footer class="footer song-details">
        <div class="container">
            <div class="content has-text-centered">
                <h3><strong>{{song.song_name}} — Single</strong> by <strong> {{song.artiste}} </strong></h3>
                <div class="columns">
                    <div class="column is-7">
                        <img :src="song.cover_art" class="album-image">
                    </div>

                    <div class="column is-5">
                        <p class="footer-text">
                            <ul class="song-list">
                                <li class="song">
                                	💿 &nbsp; {{song.song_name}}

                                	<a :href="song.link" @click.prevent="downloadSong" class="button is-danger" v-if="!songPath"> 
                                        Initialize Download
                                    </a>
        
                                    <form method="POST" action="/soundcloud/serve-user-download" v-if="songPath && song.service=='soundcloud'" class="dl-form">  
                                    	<input type="hidden" name="songPath" :value="songPath">
                                    	<button type="submit">
                                    		<span class="icon has-text-info">
                                            	<i class="fa fa-cloud-download"></i>
                                        	</span> 
                                    	</button>
                                   	</form>

                                   	<form method="POST" action="/bandcamp/serve-user-download" v-if="songPath && song.service=='bandcamp'" class="dl-form">  
                                    	<input type="hidden" name="songPath" :value="songPath">
										<button type="submit">
                                    		<span class="icon has-text-info">
                                            	<i class="fa fa-cloud-download"></i>
                                        	</span> 
                                    	</button>
                                   	</form>
                                </li>
                            </ul>
                            
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <loading-modal v-if="loading" :status="statusMessage"> </loading-modal>
        <error-modal v-if="error" :status="errorMessage"> </error-modal>
        <!-- <success-modal v-if="success" :status="successMessage"> </success-modal> -->
    </footer>

</template>


<script type="text/javascript">
	import LoadingModal from './LoadingModal.vue';
	import SuccessModal from './SuccessModal.vue';
	import ErrorModal from './ErrorModal.vue';
	export default{
		data: function(){
			return{
				loading: false,
				success: false,
				error: false,
				statusMessage: '',
				successMessage: '',
				errorMessage: '',
				songPath: ''
			}
		},
		created(){
			Event.$on('successClose', ()=>{
				this.success = false;
				this.successMessage = '';
				this.error = '';
			});

			Event.$on('errorClose', ()=>{
				this.errorMessage = '';
				this.error = '';
			});
		},
		props: ["song"],
		methods: {
			downloadSong: function(){
				self = this;
				this.loading = true;
				this.statusMessage = "Downloading to server... please be patient";

				if(this.song.service ==='soundcloud'){
					axios.post("soundcloud/download", {
						link: this.song.link,
						details: this.song
					})
					.then((data)=>{
						self.loading = false;
						self.statusMessage = '';
						self.success = true;
						self.successMessage = `Done downloading to server. Click on the cloud dl button to download`;
						// console.log(data);
						self.songPath = data.data.songPath;
					})
					.catch((e)=>{
						self.loading = false;
						self.error = true;
						self.errorMessage = "Baba error dey yapa!";
						console.log(e.response);
					})
					return;
				}

				axios.post("/bandcamp/download", {
					link: this.song.link,
					details: this.song
				})
				.then((data)=>{
					self.loading = false;
					self.statusMessage = '';
					self.success = true;
					self.successMessage = `Successfully Downloaded ${self.song.song_name} by ${self.song.artiste}`;
					// console.log(data);
					self.songPath = data.data.songPath;
				})
				.catch((e)=>{
					self.loading = false;
					self.error = true;
					self.errorMessage = "Baba error dey yapa!";
					console.log(e.response);
				})
			},
		},
		components: {LoadingModal, SuccessModal, ErrorModal}
	}
</script>