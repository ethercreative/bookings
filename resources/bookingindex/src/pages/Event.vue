<template>
	<div :class="$style.wrap" v-if="event">
		<bookings-header
			back="Back to events"
			to="/"

			:heading="event.title"
			description="[Maecenas faucibus mollis interdum. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.]"
		/>

		<div :class="$style.filter">
			<bookings-select
				@change="onSelectChange"
				:options="[
					{ label: 'All Slots', value: '*' },
					{ label: '10:00 - 22nd October', value: '10:00' },
				]"
			/>
			<search
				:perform-search="search"
				:busy="busy"
				placeholder="Search Bookings"
			/>
			<bookings-button
				label="Export"
				@click="doExport"
			/>
		</div>

		<sortable-table :data="allBookings">
			<column label="ID" handle="id" :render="renderId" />
			<column label="Name" handle="customerName" />
			<column label="Email" handle="customerEmail" />
			<column label="Order" handle="orderId" :render="renderOrder" />
			<column label="Date Booked" handle="dateBooked" :render="renderDate" />
		</sortable-table>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import BookingsHeader from '../components/BookingsHeader';
	import Search from '../components/Search';
	import BookingsSelect from '../components/BookingsSelect';
	import BookingsButton from '../components/BookingsButton';
	import { SortableTable, Column } from '../components/SortableTable/index.js';
	import formatDate from '../helpers/formatDate';

	export default {
		name: 'Event',

		components: {
			BookingsHeader,
			Search,
			BookingsSelect,
			SortableTable,
			Column,
			BookingsButton,
		},

		data: () => ({
			busy: false,
		}),

		computed: {
			...mapState([
				'events',
				'bookings',
				'bookingsByEventId',
			]),

			event () {
				return this.events[this.$route.params.eventId];
			},

			allBookings () {
				const bookingIds = this.bookingsByEventId[this.$route.params.eventId];
				if (!bookingIds) return [];
				return bookingIds.map(id => this.bookings[id]);
			}
		},

		mounted () {
			const { eventId } = this.$route.params;
			this.$store.dispatch('getEvent', { eventId });
			this.$store.dispatch('getBookings', { eventId });
		},

		methods: {

			// Actions
			// -----------------------------------------------------------------

			onSelectChange (/*e*/) {
				// TODO: Filter
				this.busy = true;
				setTimeout(() => {
					this.busy = false;
				}, 1000);
			},

			search (query, done) {
				// TODO: Search
				setTimeout(done, 1000);
			},

			async doExport () {
				const { eventId } = this.$route.params;
				const a = document.createElement('a');
				a.setAttribute(
					'href',
					Craft.getActionUrl('bookings/api/export') + '&eventId=' + eventId
				);
				a.setAttribute(
					'download',
					eventId + '.csv'
				);
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
			},

			// Render
			// -----------------------------------------------------------------

			renderId (row) {
				return (
					<a href={`/bookings/booking/${row.id}`}>
						#{row.id}
					</a>
				);
			},

			renderOrder (row) {
				return (
					<a href={`/admin/commerce/orders/${row.orderId}`}>
						#{row.orderId}
					</a>
				);
			},

			renderDate (row, column) {
				return formatDate(
					row[column.handle],
					window.bookingsDateTimeFormat
				);
			},
		},
	};
</script>

<style lang="less" module>
	@import "../variables";

	.filter {
		position: sticky;
		top: -15px;

		display: flex;

		background-color: #fff;

		> * {
			&:first-child {
				margin-right: 5px;
			}

			&:last-child {
				margin-left: 5px;
			}

			&:not(:first-child):not(:last-child) {
				margin-left: 5px;
				margin-right: 5px;
			}
		}
	}
</style>