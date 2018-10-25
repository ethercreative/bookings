<script>
	import resolve from '../../helpers/resolve';

	export default {
		name: 'TableCell',
		functional: true,

		props: {
			column: Object,
			row: Object,
		},

		render (h, { props: { row, column } }) {
			if (column.render)
				return <td>{column.render(row, column)}</td>;

			return <td>{resolve(column.handle, row)}</td>;
		},
	};
</script>