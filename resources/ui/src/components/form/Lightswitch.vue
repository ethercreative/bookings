<template>
	<label>
		<input
			type="checkbox"
			:name="name"
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
			name: String,
		},
	}
</script>

<style module lang="less">
	//
</style>