<script>
	export default {
		name: 'TableHeader',
		functional: true,

		props: {
			column: Object,
		},

		render (h, { props }) {
			return (
				<th role="columnheader">
					{props.column.label}
				</th>
			);
		},
	};
</script>

<style lang="less" module>

</style>