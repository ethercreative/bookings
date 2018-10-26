<script>
	export default {
		name: 'Column',

		props: {
			label: {
				required: true,
				type: [String, Function, Object],
			},
			handle: {
				required: true,
				type: String,
			},
			render: {
				required: false,
				type: Function,
			},
			styles: {
				required: false,
				type: Object,
			},
		},

		render () {
			return null;
		},
	};
</script>