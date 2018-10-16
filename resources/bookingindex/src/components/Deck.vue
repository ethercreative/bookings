<template>
	<div :class="$style.deck">
		<slot />
	</div>
</template>

<script>
	export default {
		name: "Deck"
	};
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