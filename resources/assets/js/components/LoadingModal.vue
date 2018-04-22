<template>
	<div class="modal is-active">
		<div class="modal-background"> </div>
		<div class="modal-content">
			<div class="spinner .spinner-wave">
			  <div class="rect1"></div>
			  <div class="rect2"></div>
			  <div class="rect3"></div>
			  <div class="rect4"></div>
			  <div class="rect5"></div>
			</div>
			<p class="status">{{status}}</p>
    	</div>
		<button class="modal-close is-large" aria-label="close" @click="close"></button>
	</div>
</template>

<script>
	export default{
		created(){
			
		},
		props: ['status'],
		methods: {
			close(){
				Event.$emit('modalClose');
			}
		}
	}
</script>

<style type="text/css">
	.modal-content{
		height: 16000px;
		overflow-x: hidden;
	}

	.spinner-wave{
		position: relative;
		top: 160px;
	}

	p.status{
		font-family: Montserrat;
		position: relative;
		top: -80px;
		text-align: center;
		font-size: 20px;
		left: -10px;
		color: white;
	}
</style>

