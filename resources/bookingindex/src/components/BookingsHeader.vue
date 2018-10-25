<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component({
	props: {
		heading: String,
		description: String,

		back: String,
		to: String,
	},
})
export default class Header extends Vue {

	// Render
	// =========================================================================

	render () {
		const { back, to, heading, description } = this.$props;

		return (
			<header class={this.$style.header}>
				<div class={this.$style.heading}>
					{back && to && (
						<router-link to={to}>
							← {back}
						</router-link>
					)}
					<h1>{heading}</h1>
					<p>{description}</p>
				</div>

				{this.$slots.default}
			</header>
		);
	}

}
</script>

<style lang="less" module>
	.header {
		display: grid;
		grid-template-columns: repeat(12, 1fr);
		grid-gap: 30px;
		padding: 40px 30px;

		background-color: #F1F5F8;
		border-bottom: 1px solid #D9DDE2;
	}

	.heading {
		grid-column: span 6;

		a {
			display: inline-block;
			margin-bottom: 12px;

			color: #75889E;
			font-size: 14px;
			letter-spacing: 0;
			line-height: 26px;
		}

		h1 {
			font-size: 44px;
		}

		p {
			color: #75889E;
			font-size: 14px;
			letter-spacing: 0;
			line-height: 26px;
		}
	}
</style>