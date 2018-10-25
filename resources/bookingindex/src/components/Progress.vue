<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component({
	props: {
		width: {
			type: Number,
			default: 0,
		},
	},
})
export default class Progress extends Vue {

	// Render
	// =========================================================================

	render () {
		return (
			<span class={this.$style.bar}>
				<span
					class={this.$style.fill}
					style={{width: (this.$props.width * 100) + '%'}}
				/>
			</span>
		);
	}

}
</script>

<style lang="less" module>
	.bar,
	.fill {
		display: block;
		width: 100%;
		height: 8px;

		background-color: #E7EEF3;
		border-radius: 2px;
	}

	.fill {
		background-color: #2667CE;
	}
</style>