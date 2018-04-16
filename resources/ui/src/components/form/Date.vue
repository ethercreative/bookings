<template>
	<label>
		<input
			type="text"
			@focus="openModal"
			ref="input"
		/>

		<Modal
			:open="isOpen"
			:on-request-close="onRequestClose"
			:clear="true"
			:parented="true"
		>
			oh hi
		</Modal>
	</label>
</template>

<script>
	import Modal from "../Modal";

	export default {
		name: "Date",
		components: { Modal },

		data () {
			return {
				isOpen: false,
			};
		},

		methods: {

			openModal () {
				this.$refs.input.blur();
				this.isOpen = true;
			},

			onRequestClose () {
				this.isOpen = false;
			},

		},
	};
</script>

<style module lang="less">
	.date {
		//
	}
</style>