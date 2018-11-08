<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component({
	props: {
		isOpen: {
			type: Boolean,
			required: true,
		},
		whenRequestClose: {
			type: Function,
			required: true,
		},
	}
})
export default class Modal extends Vue {

	// Vue
	// =========================================================================

	beforeCreate () {
		if (!document.getElementById('modal-container')) {
			const el = document.createElement('div');
			el.setAttribute('id', 'modal-container');
			document.body.appendChild(el);
		}
	}

	beforeMount () {
		window.addEventListener('keydown', this.onKeyDown, true);
	}

	beforeDestroy () {
		window.removeEventListener('keydown', this.onKeyDown, true);
	}

	// Events
	// =========================================================================

	onKeyDown (e) {
		if (e.key !== 'Escape')
			return;

		e.preventDefault();
		this.$props.whenRequestClose();
	}

	onOverlayClick (e) {
		if (e.target !== this.$refs.overlay)
			return;

		e.preventDefault();

		this.$props.whenRequestClose();
	}

	// Render
	// =========================================================================

	render () {
		return (
			<portal target-el="#modal-container">
				<transition
					duration={150}
					enterClass={this.$style.hide}
					leaveToClass={this.$style.hide}
				>
					{this.$props.isOpen && (
						<div
							class={this.$style.overlay}
							ref="overlay"
							onClick={this.onOverlayClick}
						>
							<div class={this.$style.modal}>
								{this.$slots.default}
							</div>
						</div>
					)}
				</transition>
			</portal>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.overlay {
		position: fixed;
		z-index: 100;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;

		display: flex;
		align-items: center;
		justify-content: center;
		padding: @spacer;

		background-color: rgba(0,0,0,0.4);

		overflow: auto;

		transition: opacity 0.15s ease;

		&.hide {
			opacity: 0;
			pointer-events: none;

			.modal {
				transform: scale(0.9);
			}
		}
	}

	.modal {
		width: 100%;
		max-width: 450px;
		margin: auto;

		background: #fff;
		box-shadow: 0 2px 20px 0 rgba(0, 0, 0, 0.30);
		border-radius: 4px;

		transition: transform 0.15s ease;
	}
</style>