<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component({
	props: {
		options: Array,
	},
})
export default class Select extends Vue {

	// Events
	// =========================================================================

	onChange (e) {
		this.$emit('change', e);
	}

	// Render
	// =========================================================================

	render () {
		return (
			<label class={this.$style.label}>
				<select
					class={this.$style.select}
					onChange={this.onChange}
				>
					{this.$props.options.map((opt, i) => (
						<option
							key={i}
							value={opt.value}
						>
							{opt.label}
						</option>
					))}
				</select>
			</label>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.label {
		position: relative;
		display: block;
		margin: 30px;

		&:before,
		&:after {
			content: '';
			position: absolute;
			z-index: 2;

			top: 50%;
			right: 12px;

			border: 3px solid transparent;
			pointer-events: none;
		}

		&:before {
			margin-top: 2px;

			border-top: 6px solid #8495A8;
			border-bottom: none;
		}

		&:after {
			margin-top: -8px;

			border-top: none;
			border-bottom: 6px solid #8495A8;
		}
	}

	.select {
		position: relative;
		z-index: 1;

		padding: 9px 30px 9px 9px;

		color: @text-color;
		font-size: 14px;
		font-family: @font-family;

		appearance: none;
		background: #fff;
		border: 1px solid #D9DDE2;
		border-radius: 3px;

		transition: border-color 0.15s ease;

		&:focus {
			border-color: @primary;
		}
	}
</style>