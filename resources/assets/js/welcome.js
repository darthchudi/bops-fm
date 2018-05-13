require('./bootstrap.js');
window.Vue = require('vue');
import LoadingModal from './components/LoadingModal.vue';
import SuccessModal from './components/SuccessModal.vue';
import ErrorModal from './components/ErrorModal.vue';
import SongBox from './components/SongBox.vue';
import AlbumBox from './components/AlbumBox.vue';
window.Event = new Vue();

var app = new Vue({
	el: "#root",
	data: {
		status: "",
		loading: false,
		linkDetails: ['', 'fa-music', ''],
		link: '',
		success: false,
		successMessage: "",
		error: false,
		errorMessage: "",
		fetchedSong: false,
		songDetails: {},
		songPath: "",
		albumDetails: {},
		albumTracklist: [],
		isAlbum: '',
		fetchedAlbum: ''
	},
	created(){
		Event.$on('modalClose', ()=>{
			this.loading = false;
		});

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
	watch: {
		link: function(){
			this.evaluateLink();
		}
	},
	methods: {
		submit: function(){
			if(this.link==''){
				return;
			}
			this.status = "Fetching bops..."
			this.fetchedSong = false;
			this.fetchedAlbum = false;
			this.loading = true;
			self = this;

			//Soundcloud
			if(this.linkDetails[0]=='soundcloud'){
				axios.post('/soundcloud/fetchLink', {
					url: self.link
				})
				.then((data)=>{
					self.link = '';
					self.loading = false;
					if(data.data.kind=='song'){
						self.status = '';
						self.link = '';
						self.loading = false;
						self.fetchedSong = true;
						self.songDetails = data.data;
						return;
					}

					if(data.data.kind=='playlist'){
						self.fetchedAlbum = true;
						self.loading = false;
						self.albumDetails = data.data;
						self.albumTracklist = data.data.tracklist;
					}
					// console.log(data);
				})
				.catch((e)=>{
					this.loading = false;
					this.error = true;
					self.errorMessage = "Oops! An error occured while getting bop"
					console.log(e.response);
				})
			}

			//Bandcamp
			if(this.linkDetails[0]=='bandcamp'){
				if(this.linkDetails[2]=='single'){
					axios.post('/bandcamp/single/fetchLink', {
						url: self.link
					})
					.then((data)=>{
						self.status = '';
						self.link = '';
						self.loading = false;
						self.fetchedSong = true;
						self.songDetails = data.data.metaData;
					})
					.catch((e)=>{
						this.loading = false;
						this.error = true;
						self.errorMessage = "Oops! An error occured while getting bop"
						console.log(e.response);
					})
				}

				if(this.linkDetails[2]=='album'){
					axios.post('/bandcamp/album/fetchLinks', {
						url: self.link
					})
					.then((data)=>{
						self.fetchedAlbum = true;
						self.loading = false;
						var tracksAndLinks = []; 
						var tracklist = data.data.metaData.tracklist;
						var links = data.data.metaData.links;
						var tracklistLength = tracklist.length;
						var linksLength = tracklist.length;

						if(!tracklistLength===linksLength){
							console.log("Tracklist-Links inaccuracy");
							return;
						}

						for(var i=0; i<=linksLength-1; i++){
							tracksAndLinks.push({
								name: tracklist[i],
								link: links[i],
								trackNumber: i+1
							});
						}
						self.albumDetails = data.data.metaData;
						self.albumTracklist = tracksAndLinks;
					})
					.catch((e)=>{
						console.log(e.response);
					})
				}

				
			}
		},
		evaluateLink: function(){
			var soundcloud = 'soundcloud';
			var bandcamp = 'bandcamp';

			if(this.link.match(soundcloud)){
				this.linkDetails[0] = 'soundcloud';
				this.linkDetails[1] = 'fa-soundcloud';
				return;
			}

			if(this.link.match(bandcamp)){
				this.linkDetails[0] = 'bandcamp';
				this.linkDetails[1] = 'fa-bandcamp';
				var bandcampAlbumPattern = /bandcamp.com\/album\//;
				var bandcampSinglePattern = /bandcamp.com\/track\//;

				if(this.link.match(bandcampSinglePattern)){
					this.linkDetails[2] = 'single';
				}

				if(this.link.match(bandcampAlbumPattern)){
					this.linkDetails[2] = 'album';
				}
				return;
			}

			this.linkDetails[0] = '';
			this.linkDetails[1] = 'fa-music';
		},
	},
	components: {LoadingModal, SuccessModal, ErrorModal, SongBox, AlbumBox}
})