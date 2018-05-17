<template>
	<div>
		<div class="modal fade show showModal" id="exampleModalCenter" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="spinner spinner-wave">
				  <div class="rect1"></div>
				  <div class="rect2"></div>
				  <div class="rect3"></div>
				  <div class="rect4"></div>
				  <div class="rect5"></div>
				</div> <br/>
			</div>
		</div>
		<div class="modal-backdrop fade show"></div>
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
