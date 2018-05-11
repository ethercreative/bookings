<template>
	<span :class="['field', $style.nm]">
		<span class="heading">
			<label>{{label}}</label>
			<span class="instructions">{{instructions}}</span>
		</span>
	</span>
</template>

<script>
	export default {
		name: "CraftFieldHeading",

		props: {
			label: String,
			instructions: String,
		}
	};
</script>

<style module lang="less">
	.nm {
		display: block;
		margin: 0 !important;

		span {
			display: block;
		}
	}
</style>