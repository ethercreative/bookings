<script>
import Vue from 'vue';
import Component from 'vue-class-component';

@Component
export default class Tabs extends Vue {

	// Render
	// =========================================================================

	render () {
		return (
			<nav class={this.$style.tabs}>
				<ul>
					<li>
						<router-link to="/" exactActiveClass={this.$style.active}>
							Active Events
						</router-link>
					</li>
					<li>
						<router-link to="/past" exactActiveClass={this.$style.active}>
							Past Events
						</router-link>
					</li>
				</ul>
			</nav>
		);
	}

}
</script>

<style lang="less" module>
	.tabs {
		grid-column: span 12;
		margin-bottom: -41px;

		font-size: 0;

		ul {
			list-style: none;
		}

		li {
			display: inline-block;
			margin-right: 5px;
		}

		a {
			display: block;
			padding: 15px 40px;

			color: #2C3744;
			font-size: 14px;
			letter-spacing: 0;
			text-align: center;
			text-decoration: none;

			border: 1px solid transparent;
			border-bottom: none;
			border-radius: 3px 3px 0 0;

			opacity: 0.5;
			transition: opacity 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;

			&:hover,
			&.active {
				opacity: 1;
				background-color: #fff;
				border-color: #D9DDE2;
			}
		}
	}
</style>