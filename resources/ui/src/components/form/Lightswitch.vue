<template>
	<label>
		<input
			type="checkbox"
			:checked="value"
			@change="$emit('input', $event.target.checked)"
		/>
	</label>
</template>

<script>
	export default {
		name: "Lightswitch",
		props: {
			value: null,
			checked: Boolean,
		},
	}
</script>

<style module lang="less">
	//
</style>