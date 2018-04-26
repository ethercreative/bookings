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
					<RRuleBlock
						slot="header"
						:disabled="true"
						:rrule="baseRule"
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
	</Modal>
</template>

<script>
	import { mapState } from "vuex";
	import Draggable from "vuedraggable";
	import Modal from "../components/Modal";
	import RRuleBlock from "../components/RRuleBlock";
	import Button from "../components/form/Button";

	export default {
		name: "ExceptionsModal",
		props: ["open", "onRequestClose"],
		components: { Modal, Draggable, RRuleBlock, Button },

		// Computed
		// =====================================================================

		computed: {
			...mapState([
				"baseRule",
				"exceptions",
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

		}
	}
</script>

<style module lang="less">
	@import "../variables";

	// Sidebar
	// =========================================================================

	.sidebar {
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
		border: 1px solid #DADFEA;
		border-radius: 3px;

		cursor: pointer;
		transition: color 0.15s ease;

		&:hover {
			color: #959FB8;
		}
	}
</style>