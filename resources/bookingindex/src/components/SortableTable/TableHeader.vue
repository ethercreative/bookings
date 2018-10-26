<script>
	export default {
		name: 'TableHeader',
		functional: true,

		props: {
			column: Object,
		},

		render (h, { props }) {
			const label = props.column.label;

			return (
				<th role="columnheader" style={props.column.styles}>
					{typeof label === 'function' ? label() : label}
				</th>
			);
		},
	};
</script>

<style lang="less" module>

</style>