<template>
	<th
		role="columnheader"
	>{{column.label}}</th>
</template>

<script>
	export default {
		name: 'TableHeader',

		props: {
			column: Object,
		},
	};
</script>

<style lang="less" module>

</style>