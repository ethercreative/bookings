<template>
	<transition
		name="fade"
		@before-enter="onBeforeEnter"
		@after-enter="onAfterEnter"
		@after-leave="onAfterLeave"
	>
		<div
			:class="$style.overlay"
			role="dialog"
			aria-modal="true"

			v-if="open"
			@click.self="onRequestClose"
		>
			<div :class="$style.modal">
				<slot></slot>
			</div>
		</div>
	</transition>
</template>

<script>
	import FocusManager from "../helpers/FocusManager";

	export default {
		name: "Modal",
		props: ["open", "onRequestClose"],

		mounted () {
			// Bind Events
			document.body.addEventListener("keyup", this.onBodyKeyUp);
		},

		beforeDestroy () {
			// Unbind Events
			document.body.removeEventListener("keyup", this.onBodyKeyUp);
		},

		methods: {

			/**
			 * @param {KeyboardEvent} e
			 */
			onBodyKeyUp: function (e) {
				if (e.keyCode === 27)
					this.onRequestClose();
			},

			onBeforeEnter: function () {
				document.body.style.overflow = "hidden";
			},

			onAfterEnter: function () {
				FocusManager.setup(this.$el);
			},

			onAfterLeave: function () {
				document.body.style.overflow = "";
			},

		}

	}
</script>

<style module lang="less">
	.overlay {
		position: fixed;
		z-index: 100;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;

		padding: 30px;

		background: fade(#fff, 75);
	}

	.modal {
		position: relative;

		display: flex;
		align-items: stretch;
		width: 100%;
		height: 100%;

		background-color: #fff;
		border-radius: 5px;
		box-shadow: 0 25px 100px rgba(0, 0, 0, 0.5);

		overflow: hidden;
	}
</style>

<!-- Non-moduled css -->
<style scoped>
	.fade-enter-active, .fade-leave-active {
		transition: opacity 0.2s ease;
	}

	.fade-enter, .fade-leave-to {
		opacity: 0;
	}
</style>