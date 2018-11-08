<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component({
	props: ['label', 'small', 'wide'],
})
export default class Button extends Vue {

	// Render
	// =========================================================================

	render () {
		const { label, small, wide } = this.$props;

		return (
			<button
				class={[
					this.$style.btn,
					{
						[this.$style.small]: small,
						[this.$style.wide]: wide,
					}
				]}
				onClick={e => this.$emit('click', e)}
			>
				{label}
			</button>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.btn {
		display: inline-block;
		margin: @spacer;
		padding: 10px 20px;
		box-sizing: border-box;

		color: #fff;
		font-size: 14px;
		font-weight: 500;

		appearance: none;
		background: linear-gradient(#da5a47, #cc5643, #bf503f, #b34b3b, #b34b3b, #bf503f);
		background-size: 100% 300%;
		border: none;
		border-radius: 3px;
		cursor: pointer;

		transition: background-position 0.15s ease;

		&:hover {
			background-position: 0 50%;
		}

		&:active {
			background-position: 0 100%;
		}

		&.small {
			margin: 0;
			padding: 7px 15px;
		}

		&.wide {
			width: calc(~"100% - "@spacer*2);

			&.small {
				width: 100%;
			}
		}
	}
</style>