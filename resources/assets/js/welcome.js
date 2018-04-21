require('./bootstrap.js');
window.Vue = require('vue');
import LoadingModal from './components/LoadingModal.vue';
import SuccessModal from './components/SuccessModal.vue';
import ErrorModal from './components/ErrorModal.vue';
window.Event = new Vue();

var app = new Vue({
	el: "#root",
	data: {
		status: "fetching bops...",
		loading: false,
		linkDetails: ['', 'fa-music'],
		link: '',
		success: false,
		successMessage: "Successfully Downloaded ANU By Mike",
		error: false,
		errorMessage: ""
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

			self = this;
			this.loading = true;
			axios.post('/download', {
				url: self.link
			})
			.then((data)=>{
				self.link = '';
				self.loading = false;
				var songDetails = data.data;
				self.successMessage = `Successfully downloaded ${songDetails['song_name']} by ${songDetails['artiste']}`;
				self.success = true;
				console.log(data);
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
	components: {LoadingModal, SuccessModal, ErrorModal}
})