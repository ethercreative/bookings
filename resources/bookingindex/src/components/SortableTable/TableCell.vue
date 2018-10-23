<script>
	export default {
		name: 'TableCell',
		functional: true,

		props: {
			column: Object,
			row: Object,
		},

		render (h, { props }) {
			const template = props.column.$scopedSlots.default;

			if (template)
				return h('td', {}, template({ row: props.row }));

			return h('td', {}, props.row[props.column.handle].toString());
		},
	};
</script>