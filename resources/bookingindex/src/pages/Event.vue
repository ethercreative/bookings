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
		</div>

		<sortable-table
			:data="allBookings"
		>
			<column label="ID" handle="id">
				<template slot-scope="{ row }">
					<a href="#">{{row.id}}</a>
				</template>
			</column>

			<column label="Name" handle="name" />

			<column label="Email" handle="email" />

			<column label="Order" handle="order">
				<template slot-scope="{ row }">
					<a :href="'/admin/commerce/orders/' + row.order">
						{{row.order}}
					</a>
				</template>
			</column>

			<column label="Date Booked" handle="dateBooked">
				<template slot-scope="{ row }">
					{{formatDate(row.dateBooked)}}
				</template>
			</column>
		</sortable-table>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import BookingsHeader from '../components/BookingsHeader';
	import Search from '../components/Search';
	import BookingsSelect from '../components/BookingsSelect';
	import { SortableTable, Column } from '../components/SortableTable/index.js';
	import formatDate from "../../../ui/src/_helpers/formatDate";

	export default {
		name: 'Event',

		components: {
			BookingsHeader,
			Search,
			BookingsSelect,
			SortableTable,
			Column,
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

			formatDate (date) {
				return formatDate(date, window.bookingsDateTimeFormat);
			}
		},
	};
</script>

<style lang="less" module>
	@import "../variables";

	.filter {
		display: flex;

		label {
			&:first-child {
				margin-right: 5px;
			}

			&:last-child {
				margin-left: 5px;
			}
		}
	}
</style>