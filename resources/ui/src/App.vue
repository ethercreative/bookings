<template>
	<div id="app">
		Hello world!
	</div>
</template>

<script>
	export default {
		name: "app",
		components: {},
	}
</script>

<style scoped lang="less">
	@import "variables";

	#app {
		font-family: 'Avenir', Helvetica, Arial, sans-serif;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		text-align: center;
		color: @col;
		margin-top: 60px;
	}
</style>
