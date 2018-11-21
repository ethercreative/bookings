<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import { get } from '../helpers/fetch';

@Component({
	props: {
		bookingId: {
			type: Number,
			required: true,
		},
	},
})
export default class TicketFields extends Vue {

	// Properties
	// =========================================================================

	html = null;

	// Vue
	// =========================================================================

	async mounted () {
		this.html = await get(
			'bookings/cp/ticket-fields',
			{ id: this.$props.bookingId },
			true
		);
	}

	// Render
	// =========================================================================

	render () {
		if (this.html === null)
			return <p>Loading...</p>;

		return (
			<div domPropsInnerHTML={this.html} class={this.$style.customFieldsWrap} />
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.customFieldsWrap {
		fieldset {
			margin-bottom: @spacer/2;
			padding: @spacer;

			border: 1px solid @border;
			border-radius: 4px;
		}

		h4 {
			margin-bottom: @spacer/2 !important;

			color: @text-color;
			font-weight: 300;
			font-size: 16px;
			line-height: 1;
		}
	}
</style>