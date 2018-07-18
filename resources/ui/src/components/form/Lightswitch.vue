<template>
	<div>
		<input
			type="checkbox"
			:name="name"
			:id="name"
			:checked="value"
			class="checkbox"
			@change="$emit('input', $event.target.checked)"
		/>
		<label :for="name"></label>
	</div>
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