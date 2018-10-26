<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import TableHeader from './TableHeader';
import TableCell from "./TableCell";

@Component({
	props: {
		data: {
			type: Array,
			required: true,
		},
	},
})
export default class SortableTable extends Vue {

	// Properties
	// =========================================================================

	columns = [];

	// Vue
	// =========================================================================

	mounted () {
		this.columns =
			this.$slots.default
				.filter(el => el.componentInstance)
				.map(el => el.componentInstance);
	}

	// Render
	// =========================================================================

	render () {
		return (
			<div class={this.$style['table-wrap']}>
				<table
					class={this.$style.table}
					cellPadding="0"
					cellSpacing="0"
				>
					<thead>
						<tr>
							{this.columns.map(column => (
								<TableHeader
									key={column.handle}
									column={column}
								/>
							))}
						</tr>
					</thead>
					<tbody>
						{this.$props.data.map((row, rowIndex) => (
							<tr key={rowIndex}>
								{this.columns.map((column, colIndex) => (
									<TableCell
										key={colIndex * rowIndex}
										row={row}
										column={column}
									/>
								))}
							</tr>
						))}
					</tbody>
				</table>

				{/* Required for columns to work :( */}
				{this.$slots.default}

				{/* TODO: Infinite scroll w/ option to switch to pagination */}
			</div>
		);
	}

}
</script>

<style lang="less" module>
	@import "../../variables";

	.table-wrap {
		margin: 0 @spacer @spacer*2;
	}

	.table {
		width: 100%;

		thead tr {
			position: sticky;
			top: 69px;
		}

		th {
			padding: 6px 10px;

			color: #858E99;
			font-weight: 400;

			background-color: #F1F5F8;
			border-collapse: collapse;

			&:first-child {
				border-radius: 3px 0 0 0;
			}

			&:last-child {
				border-radius: 0 3px 0 0;
			}

			&:first-child:last-child {
				border-radius: 3px 3px 0 0;
			}
		}

		td {
			padding: 10px;
		}

		tbody tr:not(:last-child) td {
			border-bottom: 1px solid #E6EAED;
		}
	}
</style>