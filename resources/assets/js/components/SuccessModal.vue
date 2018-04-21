<template>
	<div class="modal is-active">
		<div class="modal-background"> </div>
		<div class="modal-content">
			<div class="success-card">
				<img src="/images/sign-check-icon.png" class="success">
				<p class="status">{{status}}</p>	
			</div>
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
				Event.$emit('successClose');
			}
		}
	}
</script>

<style type="text/css">
	.modal-content{
		height: 16000px;
		overflow-x: hidden;
		overflow-y: hidden;
	}

	.success{
		position: relative;
		left: 20%;
		width: 400px;
	}

	p.status{
		font-family: Montserrat;
		position: relative;
		top: 20px;
		left: 20%;
		font-size: 20px;
		color: #8ce07f;
	}

	.success-card{
		position: relative;
		top: 30px;
		background-color: white;
		border-radius: 30px;
		height: 470px;
	}
</style>

