<template>
	<div>
		<button @click="exceptionsModalOpen = true" type="button">
			Open exceptions modal
		</button>

		<ExceptionsModal
			:open="exceptionsModalOpen"
			:onRequestClose="onRequestClose"
		/>
	</div>
</template>

<script>
	import ExceptionsModal from "./field/ExceptionsModal";

	export default {
		name: "field",

		components: {
			ExceptionsModal,
		},

		data: () => ({
			exceptionsModalOpen: false,
		}),

		methods: {
			onRequestClose: function () {
				this.exceptionsModalOpen = false;
			},
		},
	}
</script>

<style scoped lang="less">
	//
</style>