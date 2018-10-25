<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component
export default class Deck extends Vue {

	// Properties
	// =========================================================================

	name = 'Deck';

	// Render
	// =========================================================================

	render () {
		return (
			<div class={this.$style.deck}>
				{this.$slots.default}
			</div>
		);
	}

}
</script>

<style lang="less" module>
	.deck {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		grid-gap: 30px;
		align-items: stretch;
		padding: 0 30px 30px;
	}
</style>