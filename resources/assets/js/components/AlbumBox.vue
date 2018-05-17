<template>
	<main class="bg-custom" id="albumbox">
		<div class="container pt-2 pb-5">
			<h1 class="text-center helvetica-n mb-4">
				<strong>{{albumDetails.album}} </strong> by <strong> {{albumDetails.artiste}} </strong>
			</h1>

			<div class="row">
				<div class="col-sm-6">
					<img :src="albumDetails.cover_art" class="img-fluid cover-art" alt="album art">
					<a @click.prevent="makeZip" class="mt-3 w-100 btn btn-success rounded-0 dl-prompt" v-if="!doesZipFileExist">
                        Download all songs as a zip file
                    </a>

                    <form method="POST" action="/download-zip">
                    	<input type="hidden" name="zipFilePath" :value="zipFilePath">
                    	<input type="submit" name="download zip" value="zip file ready for download" class="mt-3 w-100 btn btn-success rounded-0 dl-prompt animated pulse infinite" v-if="doesZipFileExist" @click="resetData">
                    </form>
				</div>

				<div class="col-sm-6 mt-3 mt-md-0">
					<ul class="list-unstyled album">
						<li class="song helvetica-n pb-3 mb-3 mb-md-4" v-for="song in tracklist">
							💿 &nbsp; {{song.name}}
							<a class="mt-3 w-100 btn btn-outline-pink rounded-0 text-white" :href="song.link" @click.prevent="downloadSong(song)" v-if="songName !== song.name"> 
                                Initialize Download
                            </a>

                            <form method="POST" action="/soundcloud/serve-user-download" class="mt-3 text-center" v-if="songName==song.name && albumDetails.service=='soundcloud'">
                            	<input type="hidden" name="songPath" :value="songPath">
                            	<button type="submit">
                            		<span class="icon has-text-info">
                                    	<i class="fa fa-cloud-download"></i>
                                	</span> 
                            	</button>
                           	</form>

                           	<form method="POST" action="/bandcamp/serve-user-download" class="mt-3 text-center" v-if="songName==song.name && albumDetails.service=='bandcamp'">  
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
	</main>
</template>


<script type="text/javascript">
	import LoadingModal from './LoadingModal.vue';
	export default{
		data: function(){
			return{
				loading: false,
				success: false,
				error: false,
				statusMessage: '',
				successMessage: '',
				errorMessage: '',
				songPath: '',
				songName: '',
				doesZipFileExist: false,
				zipFilePath: ''
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
		props: ["albumDetails", "tracklist"],
		methods: {
			downloadSong: function(song){
				self = this;
				this.loading = true;
				this.statusMessage = "Downloading Bop... plix be patient";
				var details = {
					artiste: this.albumDetails.artiste,
					album: this.albumDetails.album,
					song_name: song.name,
					track_number: song.trackNumber,
					cover_art: this.albumDetails.cover_art		
				}

				if(this.albumDetails.service ==='soundcloud'){
					axios.post("soundcloud/download", {
						link: song.link,
						details: details
					})
					.then((data)=>{
						self.loading = false;
						self.statusMessage = '';
						self.success = true;
						self.successMessage = `Successfully Downloaded ${details.song_name} by ${details.artiste}`;
						self.songPath = data.data.songPath;
						self.songName = data.data.details.song_name
						// console.log(data);
					})
					.catch((e)=>{
						self.loading = false;
						Event.$emit('error');
						console.log(e.response);
					})
					return;
				}

				axios.post("/bandcamp/download", {
					link: song.link,
					details: details
				})
				.then((data)=>{
					self.loading = false;
					self.statusMessage = '';
					self.success = true;
					self.successMessage = `Successfully Downloaded ${details.song_name} by ${details.artiste}`;
					self.songPath = data.data.songPath;
					self.songName = data.data.details.song_name
					// console.log(data);
				})
				.catch((e)=>{
					self.loading = false;
					Event.$emit('error');
					console.log(e.response);
				})
			},
			makeZip(){
				self = this;
				this.downloadedZip = false;
				this.loading = true;
				axios.post('/make-zip', {
					albumDetails: this.albumDetails,
					tracklist: this.tracklist
				})
				.then((data)=>{
					self.loading = false;
					self.doesZipFileExist = true;
					self.zipFilePath = data.data;
				})
				.catch((e)=>{
					self.loading = false;
					Event.$emit('error');
					console.log(e.response);
				})
			},
			resetData(){
				this.doesZipFileExist = false;
				this.zipFilePath = '';
			}
		},
		components: {LoadingModal}
	}
</script>