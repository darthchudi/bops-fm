require('./bootstrap.js');
window.Vue = require('vue');
import LoadingModal from './components/LoadingModal.vue';
window.Event = new Vue();

var app = new Vue({
	el: "#root",
	data: {
		status: "fetching bops...",
		loading: false,
		linkDetails: ['', 'fa-music'],
		link: ''
	},
	created(){
		Event.$on('modalClose', ()=>{
			this.loading = false;
		});
	},
	watch: {
		link: function(){
			this.evaluateLink();
		}
	},
	methods: {
		submit: function(){
			this.loading = true;
		},
		evaluateLink: function(){
			var soundcloud = 'soundcloud';
			var bandcamp = 'bandcamp';

			if(this.link.match(soundcloud)){
				this.linkDetails[0] = 'soundcloud';
				this.linkDetails[1] = 'fa-soundcloud';
				console.log(this.linkDetails[0]);
				return;
			}

			if(this.link.match(bandcamp)){
				this.linkDetails[0] = 'bandcamp';
				this.linkDetails[1] = 'fa-bandcamp'
				console.log(this.linkDetails[0]);
				return;
			}

			this.linkDetails[0] = '';
			this.linkDetails[1] = 'fa-music';
		}
	},
	components: {LoadingModal}
})