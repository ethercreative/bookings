<template>
	<span :class="$style.bar">
		<span :class="$style.fill" :style="{width:(width * 100) + '%'}"></span>
	</span>
</template>

<script>
	export default {
		name: 'Progress',

		props: {
			width: {
				type: Number,
				default: 0,
			}
		},
	};
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