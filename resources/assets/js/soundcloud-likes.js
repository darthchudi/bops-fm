require('./bootstrap.js');
window.Vue = require('vue');
import LoadingModal from './components/LoadingModal.vue';
import SuccessModal from './components/SuccessModal.vue';
import ErrorModal from './components/ErrorModal.vue';
import LikesBox from './components/LikesBox.vue';
window.Event = new Vue();

var app = new Vue({
	el: "#root",
	data: {
		loading: false,
		linkDetails: ['', 'fa-music'],
		profileUrl: '',
		batches: '',
		error: '',
		user: {},
		likes: [],
		fetchedLikes: false
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

		Event.$on('error', ()=>{
			this.error = true;
		});
	},
	watch: {
		profileUrl: function(){
			this.evaluateLink();
		}
	},
	methods: {
		submit: function(){
			self = this;
			if(this.profileUrl != '' && this.linkDetails[0]=='soundcloud'){
				self.loading = true;
				axios.post('/soundcloud/likes', {
					profileUrl: this.profileUrl
				})
				.then(({data})=>{
					self.loading = false;
					self.fetchedLikes = true;
					self.user = data.user;
					self.likes = data.likes;
					self.batches = data.batches
					console.log(data)
				})
				.catch((e)=>console.log(e.response))
			}
		},
		evaluateLink: function(){
			var soundcloud = 'soundcloud.com';

			if(this.profileUrl.match(soundcloud)){
				this.linkDetails[0] = 'soundcloud';
				this.linkDetails[1] = 'fa-soundcloud';
				return;
			}
			this.error = 'Not a soundcloud profile link';
		},
	},
	components: {LoadingModal, SuccessModal, ErrorModal, LikesBox}
})