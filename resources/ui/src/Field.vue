<template>
	<div>
		<RRuleBlock
			:rrule="baseRule"
			:hideFooter="true"
		/>

		<Button @click="exceptionsModalOpen = true">
			Open exceptions modal
		</Button>

		<ExceptionsModal
			:open="exceptionsModalOpen"
			:onRequestClose="onRequestClose"
		/>
	</div>
</template>

<script>
	import ExceptionsModal from "./field/ExceptionsModal";
	import RRuleBlock from "./components/RRuleBlock";
	import Button from "./components/form/Button";
	import RecursionRule from "./models/RecursionRule";

	export default {
		name: "field",

		components: { ExceptionsModal, RRuleBlock, Button },

		data: () => ({
			exceptionsModalOpen: false,
			baseRule: new RecursionRule(),
		}),

		methods: {
			onRequestClose: function () {
				this.exceptionsModalOpen = false;
			},
		},
	}
</script>

<style scoped lang="less">
	//
</style>