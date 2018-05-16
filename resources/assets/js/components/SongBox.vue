<template>
	<main class="bg-custom" id="songbox">
		<div class="container py-2">
			<h1 class="text-center helvetica-n mb-4">
				<strong>{{song.song_name}} — Single</strong> by <strong> {{song.artiste}} </strong>
			</h1>

			<div class="row">
				<div class="col-sm-6">
					<img :src="song.cover_art" class="img-fluid" alt="album art">
				</div>

				<div class="col-sm-6 mt-3 mt-md-0">
					<ul class="list-unstyled">
						<li class="song helvetica-n">
							💿 &nbsp; {{song.song_name}}
							<a :href="song.link" @click.prevent="downloadSong" class="mt-3 w-100 btn btn-outline-pink rounded-0 text-white" v-if="!songPath"> 
                                Initialize Download
                            </a>

                            <form method="POST" action="/soundcloud/serve-user-download" v-if="songPath && song.service=='soundcloud'" class="mt-3 text-center">
                            	<input type="hidden" name="songPath" :value="songPath">
                            	<button type="submit">
                            		<span class="icon has-text-info">
                                    	<i class="fa fa-cloud-download"></i>
                                	</span> 
                            	</button>
                           	</form>

                           	<form method="POST" action="/bandcamp/serve-user-download" v-if="songPath && song.service=='bandcamp'" class="mt-3 text-center">  
                            	<input type="hidden" name="songPath" :value="songPath">
								<button type="submit">
                            		<span class="icon has-text-info">
                                    	<i class="fa fa-cloud-download"></i>
                                	</span> 
                            	</button>
                           	</form>
						</li>
					</ul>
				</div>
			</div>
		</div>
        <loading-modal v-if="loading" :status="statusMessage"> </loading-modal>
        <error-modal v-if="error" :status="errorMessage"> </error-modal>
	</main>
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