<template>
	<div v-if="false"><slot /></div>
</template>

<script>
	export default {
		name: 'Column',

		props: {
			label: {
				required: true,
				type: String,
			},
			handle: {
				required: true,
				type: String,
			},
		},
	};
</script>