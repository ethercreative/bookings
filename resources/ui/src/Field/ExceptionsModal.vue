<template>
	<Modal :open="open" :onRequestClose="onRequestClose" :no-portal="true">
		<aside :class="$style.sidebar">
			<header :class="$style.sidebarHeader">
				<h2>Bookable Rules</h2>
				<p>Add rules below to either add bookable space, or remove it
					from the primary booking window</p>
			</header>

			<div :class="$style.rulesWrap">
				<div :class="$style.rules">
					<!-- Base Rule -->
					<!--:disabled="true"-->
					<RRuleBlock
						slot="header"
						:rrule="baseRule"
						no-drag
						include-duration
					/>

					<!-- Exceptions -->
					<Draggable
						v-model="exceptionsSort"
						:options="{
							animation: 150,
							handle: '.bookings--drag-handle',
						}"
					>
						<transition-group tag="div">
							<RRuleBlock
								v-for="id in exceptionsSort"
								:key="id"
								:id="id"
								:base-rule="baseRule"
							/>
						</transition-group>

						<!-- Add New Exception Rule -->
						<button
							type="button"
							slot="footer"
							:class="$style.addRuleButton"
							@click="onAddNewRule"
						>
							Add new rule
						</button>
					</Draggable>
				</div>
			</div>

			<footer :class="$style.sidebarFooter">
				<Button>Save event rules</Button>
			</footer>
		</aside>

		<div :class="$style.main">
			<header :class="$style.header">
				<ul :class="$style.tabs">
					<li>
						<button
							type="button"
							:class="{[$style.active]: activeView === 'day'}"
							@click="onChangeView('day')"
						>
							Day
						</button>
					</li>
					<li>
						<button
							type="button"
							:class="{[$style.active]: activeView === 'week'}"
							@click="onChangeView('week')"
						>
							Week
						</button>
					</li>
					<li>
						<button
							type="button"
							:class="{[$style.active]: activeView === 'month'}"
							@click="onChangeView('month')"
						>
							Month
						</button>
					</li>
					<li>
						<button
							type="button"
							:class="{[$style.active]: activeView === 'year'}"
							@click="onChangeView('year')"
						>
							Year
						</button>
					</li>
				</ul>
			</header>

			<!-- Day -->
			<div v-if="activeView === 'day'">Day View TODO</div>

			<!-- Week -->
			<week
				v-if="activeView === 'week'"
				:slots="computedSlots"
				:duration="slotDuration"
				:base-rule="baseRule"
			/>

			<!-- Month -->
			<div v-if="activeView === 'month'">Month View TODO</div>

			<!-- Year -->
			<div v-if="activeView === 'year'">Year View TODO</div>
		</div>
	</Modal>
</template>

<script>
	// TODO: Disable Day & Week view if frequency is greater than hourly

	import { mapState } from "vuex";
	import Draggable from "vuedraggable";
	import Modal from "../components/Modal";
	import RRuleBlock from "../components/RRuleBlock";
	import Button from "../components/form/Button";

	import Week from "../components/calendar/Week";

	export default {
		name: "ExceptionsModal",
		props: ["open", "onRequestClose"],
		components: { Modal, Draggable, RRuleBlock, Button, Week },

		data () {
			return {
				activeView: "week",
			};
		},

		// Computed
		// =====================================================================

		computed: {
			...mapState([
				"baseRule",
				"exceptions",
				"computedSlots",
				"slotDuration",
			]),

			exceptionsSort: {
				get () {
					return this.$store.state.exceptionsSort;
				},

				set (value) {
					this.$store.dispatch("updateExceptionsSort", value);
				},
			},
		},

		// Methods
		// =====================================================================

		methods: {

			onAddNewRule () {
				this.$store.dispatch("addException");
			},

			onChangeView (nextView) {
				this.activeView = nextView;
			},

		}
	}
</script>

<style module lang="less">
	@import "../variables";

	// Main
	// =========================================================================

	.main {
		position: relative;
		z-index: 1;

		display: flex;
		flex-direction: column;
		width: 100%;
		flex-shrink: 9999;

		& > div:last-child {
			height: calc(~"100% - 50px");
		}
	}

	// Header
	// =========================================================================

	.header {
		position: relative;
		z-index: 3;

		display: flex;
		align-items: center;
		justify-content: space-between;

		height: 50px;

		background: #F4F5F6;
		border-bottom: 1px solid @border;
	}

	.tabs {
		height: 100%;
		margin: 0 0 -1px;
		padding: 0;

		li {
			display: inline-block;
			height: 100%;
		}

		button {
			position: relative;

			display: flex;
			height: 100%;
			margin: 0;
			padding: 0 25px;

			font-size: 14px;

			appearance: none;
			background: none;
			border: none;
			border-right: 1px solid @border;
			border-bottom: 1px solid @border;
			border-radius: 0;
			cursor: pointer;
			outline: none;

			transition:
				background-color 0.15s ease,
				border-bottom-color 0.15s ease;

			&:hover {
				background-color: fade(#fff, 50%);
			}

			&.active {
				background-color: #fff;
				border-bottom-color: #fff;

				&:after {
					content: '';
					position: absolute;
					top: 100%;
					left: -10px;
					right: -10px;

					display: block;
					height: 10px;
					margin-top: 1px;

					background-image: linear-gradient(
						to bottom,
						#fff 0%,
						rgba(255, 255, 255, 0) 100%
					);

					pointer-events: none;
				}
			}

			// Square off bottom right corner of active tab
			&:before {
				content: '';
				position: absolute;
				bottom: -1px;
				right: -1px;

				display: block;
				width: 1px;
				height: 1px;

				background-color: @border;
			}
		}
	}

	// Sidebar
	// =========================================================================

	.sidebar {
		position: relative;
		z-index: 2;

		display: flex;
		flex-direction: column;
		align-items: stretch;
		width: 400px;
		padding: 30px 0;

		background: @aside-bg;
		border-right: 1px solid @border;
		box-shadow: 2px 0 6px 0 rgba(0,0,0,0.10);
	}

	.sidebarHeader {
		padding: 0 30px;

		h2 {
			margin: 0 0 10px;

			color: @craft-primary;
			font-size: 28px;
			font-weight: normal;
			letter-spacing: 0;
		}

		p {
			color: #8C97B2;
			font-size: 13px;
			letter-spacing: 0;
			line-height: 19px;
		}
	}

	.rulesWrap {
		position: relative;
		flex-grow: 1;
		display: flex;
		overflow: hidden;

		&:before,
		&:after {
			content: '';
			position: absolute;
			z-index: 2;
			left: 0;
			right: 15px;

			display: block;
			height: 25px;
		}

		&:before {
			top: 0;
			background-image: linear-gradient(
				to bottom,
				@aside-bg 0%,
				fade(@aside-bg, 0) 100%
			);
		}

		&:after {
			bottom: 0;
			background-image: linear-gradient(
				to bottom,
				fade(@aside-bg, 0) 0%,
				@aside-bg 100%
			);
		}
	}

	.rules {
		position: relative;
		z-index: 1;

		width: 100%;

		overflow: auto;
		padding: 25px 20px;
	}

	// Sidebar Footer
	// =========================================================================

	.sidebarFooter {
		padding: 15px 20px 0;
	}

	.addRuleButton {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100%;
		padding: 8px;

		color: fade(#959FB8, 50);
		font-size: 14px;
		text-align: center;

		appearance: none;
		background: #EAEDF0;
		border: 1px solid @border;
		border-radius: 3px;

		cursor: pointer;
		transition: color 0.15s ease;

		&:hover {
			color: #959FB8;
		}
	}
</style>