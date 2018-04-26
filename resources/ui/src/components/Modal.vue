<template>
	<!--<portal to="modals">-->
		<transition
			name="fade"
			@before-enter="onBeforeEnter"
			@after-enter="onAfterEnter"
			@after-leave="onAfterLeave"
		>
			<div
				:class="[$style.overlay, { [$style.clear]: clear }]"
				:style="{zIndex:zIndex}"
				role="dialog"
				aria-modal="true"

				v-if="open"
				@click.self="onRequestClose"
			>
				<div
					:class="[$style.modal, {
						[$style.clear]: clear && !parented && !shallow,
						[$style.parented]: parented,
						[$style.shallow]: shallow,
					}]"
					:style="parentedStyles"
				>
					<slot></slot>
				</div>
			</div>
		</transition>
	<!--</portal>-->
</template>

<script>
	import FocusManager from "../helpers/FocusManager";

	// Keeps track of open modals
	// Used to ensure subsequent modals appear above previous ones.
	const openModals = {count:0};

	export default {
		name: "Modal",
		props: {
			open: {
				type: Boolean,
				required: true,
			},

			onRequestClose: {
				type: Function,
				required: true,
			},

			clear: Boolean,
			parented: Boolean,
			shallow: Boolean,
		},

		data () {
			return {
				target: null,
				zIndex: 100,
			};
		},

		mounted () {
			if (this.parented) {
				this.target = this.$parent.$el;
			}

			// Bind Events
			document.body.addEventListener("keyup", this.onBodyKeyUp);
		},

		beforeDestroy () {
			// Unbind Events
			document.body.removeEventListener("keyup", this.onBodyKeyUp);
		},

		computed: {
			parentedStyles () {
				if (!this.target)
					return {};

				const box = this.target.getBoundingClientRect();

				return {
					top: box.bottom + "px",
					left: (box.right - box.width/2) + "px",
				};
			}
		},

		methods: {

			/**
			 * @param {KeyboardEvent} e
			 */
			onBodyKeyUp: function (e) {
				// Esc if this is the top modal
				if (e.keyCode === 27 && this.zIndex - 100 === openModals.count)
					this.onRequestClose();
			},

			onBeforeEnter: function () {
				openModals.count++;
				this.zIndex = 100 + openModals.count;

				document.body.style.overflow = "hidden";
			},

			onAfterEnter: function () {
				FocusManager.setup(this.$el);
			},

			onAfterLeave: function () {
				openModals.count--;
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

		will-change: true;
		transform: translate3d(0, 0, 0);
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
		will-change: true;
		transform: translate3d(0, 0, 0);
	}

	.clear {
		background-color: transparent;
		box-shadow: none;
	}

	.parented {
		position: fixed;

		width: auto;
		height: auto;

		transform: translate3d(-50%, 0, 0);
	}

	.shallow {
		box-shadow: 0 15px 60px rgba(0, 0, 0, 0.2);
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