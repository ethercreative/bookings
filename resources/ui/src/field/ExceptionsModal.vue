<template>
	<Modal :open="open" :onRequestClose="onRequestClose">
		<aside :class="$style.sidebar">
			<header :class="$style.sidebarHeader">
				<h2>Bookable Rules</h2>
				<p>Add rules below to either add bookable space, or remove it
					from the primary booking window</p>
			</header>


			<ExclusionBlock
				slot="header"
				disabled="true"
				:data="{label: 'Fixed'}"
			/>

			<Draggable
				v-model="testList"
				:options="{
					animation: 150,
					handle: '.' +$style.dragHandle,
				}"
			>
				<transition-group tag="div">
					<ExclusionBlock
						v-for="element in testList"
						:key="element.id"
						:data="element"
					/>
				</transition-group>

				<button
					type="button"
					slot="footer"
					:class="$style.addRuleButton"
				>
					Add new rule
				</button>
			</Draggable>
		</aside>
	</Modal>
</template>

<script>
	import Draggable from "vuedraggable";
	import Modal from "../components/Modal";
	import ExclusionBlock from "../components/ExclusionBlock";

	export default {
		name: "ExceptionsModal",
		props: ["open", "onRequestClose"],
		components: { Modal, Draggable, ExclusionBlock },
		data () {
			return {
				testList: [
					{ id: 1, label: "Hello" },
					{ id: 2, label: "World" },
					{ id: 3, label: "Lorem" },
					{ id: 4, label: "Ipsum" },
				]
			};
		}
	}
</script>

<style module lang="less">
	@import "../variables";

	// Sidebar
	// =========================================================================

	.sidebar {
		width: 400px;
		padding: 30px 20px;

		background: #F4F5F6;
		border-right: 1px solid @border;
		box-shadow: 2px 0 6px 0 rgba(0,0,0,0.10);

		overflow: auto;
	}

	.sidebarHeader {
		padding: 0 10px;
		margin-bottom: 25px;

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

	// Sidebar Footer
	// =========================================================================

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