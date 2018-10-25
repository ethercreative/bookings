<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component
export default class Dates extends Vue {

	// Render
	// =========================================================================

	render () {
		return (
			<ul class={this.$style.dates}>
				{Array.from({ length: 3 }, (_, i) => (
					<li key={i} class={this.$style.date}>
						{i + 10}/01/19
					</li>
				))}
				<li>and 2 more</li>
			</ul>
		);
	}

}
</script>

<style lang="less" module>
	.dates {
		margin-bottom: 9px;
		list-style: none;
		font-size: 0;

		li {
			display: inline-block;

			color: #3F4549;
			font-size: 10px;
			letter-spacing: 0;

			&:last-child:not(.date) {
				margin-left: 5px;
			}
		}
	}

	.date {
		padding: 2px 5px;
		margin: 0 3px 3px 0;

		background-color: #E7EEF3;
		border-radius: 2px;
	}
</style>