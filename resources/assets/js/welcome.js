require('./bootstrap.js');
window.Vue = require('vue');
import LoadingModal from './components/LoadingModal.vue';
import SuccessModal from './components/SuccessModal.vue';
import ErrorModal from './components/ErrorModal.vue';
import SongBox from './components/SongBox.vue';
window.Event = new Vue();

var app = new Vue({
	el: "#root",
	data: {
		status: "fetching bops...",
		loading: false,
		linkDetails: ['', 'fa-music'],
		link: '',
		success: false,
		successMessage: "",
		error: false,
		errorMessage: "",
		fetchedSong: false,
		songDetails: {},
		songPath: ""
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
			this.fetchedSong = false;
			if(this.link==''){
				return;
			}

			self = this;
			this.loading = true;
			axios.post('/bandcamp/fetchLink', {
				url: self.link
			})
			.then((data)=>{
				self.link = '';
				self.loading = false;
				self.fetchedSong = true;
				self.songDetails = data.data.metaData;
				// self.successMessage = `Successfully downloaded ${songDetails['song_name']} by ${songDetails['artiste']}`;
				// self.success = true;
				console.log(data.data);
			})
			.catch((e)=>{
				this.loading = false;
				this.error = true;
				self.errorMessage = "Oops! An error occured while getting bop"
				console.log(e);
			})
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
				return;
			}

			this.linkDetails[0] = '';
			this.linkDetails[1] = 'fa-music';
		},
	},
	components: {LoadingModal, SuccessModal, ErrorModal, SongBox}
})