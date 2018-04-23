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
                                    <a :href="song.link" @click.prevent="downloadSong"> 
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
        <loading-modal v-if="loading" :status="statusMessage"> </loading-modal>
        <error-modal v-if="error" :status="errorMessage"> </error-modal>
        <success-modal v-if="success" :status="successMessage"> </success-modal>
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
				errorMessage: ''
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
				this.statusMessage = "Downloading Bop... plix be patient";

				if(this.song.service ==='soundcloud'){
					axios.post("soundcloud/download", {
						link: this.song.link,
						details: this.song
					})
					.then((data)=>{
						self.loading = false;
						self.statusMessage = '';
						self.success = true;
						self.successMessage = `Successfully Downloaded ${self.song.song_name} by ${self.song.artiste}`;
						console.log(data);
					})
					.catch((e)=>{
						self.loading = false;
						self.error = true;
						self.errorMessage = "Baba error dey yapa!";
						console.log(e);
					})
					return;
				}

				axios.post("/bandcamp/single/download", {
					link: this.song.link,
					details: this.song
				})
				.then((data)=>{
					self.loading = false;
					self.statusMessage = '';
					self.success = true;
					self.successMessage = `Successfully Downloaded ${self.song.song_name} by ${self.song.artiste}`;
					console.log(data);
				})
				.catch((e)=>{
					self.loading = false;
					self.error = true;
					self.errorMessage = "Baba error dey yapa!";
					console.log(e);
				})
			},
		},
		components: {LoadingModal, SuccessModal, ErrorModal}
	}
</script>