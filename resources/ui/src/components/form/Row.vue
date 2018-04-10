<template>
	<div :class="$style.row">
		<slot></slot>
	</div>
</template>

<script>
	export default {
		name: "Row",
	};
</script>

<style module lang="less">
	.row {
		display: flex;

		&:not(:last-child) {
			margin-bottom: 10px;
		}

		> * {
			flex-grow: 1;
			margin-right: 10px;

			&:last-child {
				margin-right: 0;
			}
		}
	}
</style>