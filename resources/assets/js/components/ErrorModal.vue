<template>
	<div>
		<div class="modal fade show showModal"  tabindex="-1" role="dialog">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title text-center" id="exampleModalCenterTitle">An Error Occured</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body text-center">
						😞 Looks like an error occured while downloading bop. Please refresh and try again. If the problem persists contact <a href="https://twitter.com/bopsfmsupport" class="text-dark support"><span class="chudi"> @bopsfmsupport </span> </a>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-backdrop fade show"></div>
	</div>
</template>

<script>
	export default{
		methods: {
			close(){
				Event.$emit('errorClose');
			}
		}
	}
</script>