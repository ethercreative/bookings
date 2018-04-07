<template>
	<div id="app">
		Hello world!
	</div>
</template>

<script>
	export default {
		name: "app",
		components: {},
		computed: {
			$style () { return this.$options.cssModules }
		},
	}
</script>

<style lang="less">
	@col: #2c3e50;

	#app {
		font-family: 'Avenir', Helvetica, Arial, sans-serif;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		text-align: center;
		color: @col;
		margin-top: 60px;
	}
</style>
