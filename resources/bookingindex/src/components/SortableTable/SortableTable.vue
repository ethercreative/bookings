<template>
	<div :class="$style['table-wrap']">
		<table :class="$style.table" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<table-header
						v-for="column in columns"
						:key="column.handle"
						:column="column"
					/>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="(row, rowIndex) in data"
					:key="rowIndex"
				>
					<table-cell
						v-for="(column, colIndex) in columns"
						:key="colIndex * rowIndex"
						:row="row"
						:column="column"
					/>
				</tr>
			</tbody>
		</table>

		<!-- Required so we can get the column child components -->
		<slot />

		<!-- TODO: Infinite scroll w/ option to switch to pagination -->
	</div>
</template>

<script>
	import TableHeader from './TableHeader';
	import TableCell from "./TableCell";

	export default {
		name: 'SortableTable',

		components: {
			TableCell,
			TableHeader,
		},

		props: {
			data: {
				type: Array,
				required: true,
			},
		},

		data: () => ({
			columns: [],
		}),

		mounted () {
			this.columns = this.$slots.default
				.filter(el => el.componentInstance)
				.map(el => el.componentInstance);
		},
	};
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

			&:first-child,
			&:last-child {
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