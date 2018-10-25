<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import Header from '../components/BookingsHeader';
import Select from '../components/BookingsSelect';
import Search from '../components/Search';
import Button from '../components/BookingsButton';
import { SortableTable, Column } from '../components/SortableTable';
import formatDate from '../helpers/formatDate';

@Component
export default class Event extends Vue {

	// Properties
	// =========================================================================

	busy = false;

	// Getters
	// =========================================================================

	get events () {
		return this.$store.state.events;
	}

	get bookings () {
		return this.$store.state.bookings;
	}

	get bookingsByEventId () {
		return this.$store.state.bookingsByEventId;
	}

	get event () {
		return this.events[this.$route.params.eventId];
	}

	get allBookings () {
		const bookingIds = this.bookingsByEventId[this.$route.params.eventId];

		if (!bookingIds)
			return [];

		return bookingIds.map(id => this.bookings[id]);
	}

	// Vue
	// =========================================================================

	mounted () {
		const { eventId } = this.$route.params;
		this.$store.dispatch('getEvent', { eventId });
		this.$store.dispatch('getBookings', { eventId });
	}

	// Actions
	// =========================================================================

	search (query, done) {
		// TODO: Search
		setTimeout(done, 1000);
	}

	async export () {
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
	}

	// Events
	// =========================================================================

	onSelectChange () {
		// TODO: Filter
		this.busy = true;
		setTimeout(() => {
			this.busy = false;
		}, 1000);
	}

	// Render
	// =========================================================================

	render () {
		// TODO: Show loading screen
		if (!this.event)
			return null;

		return (
			<div class={this.$style.wrap}>
				<Header
					back="Back to events"
					to="/"

					heading={this.event.title}
					description="[Maecenas faucibus mollis interdum. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.]"
				/>

				<div class={this.$style.filter}>
					<Select
						onChange={this.onSelectChange}
						options={[
							{ label: 'All Slots', value: '*' },
							{ label: '10:00 - 22nd October', value: '10:00' },
						]}
					/>
					<Search
						onSearch={this.search}
						busy={this.busy}
						placeholder="Search Bookings"
					/>
					<Button
						label="Export"
						onClick={this.export}
					/>
				</div>

				<SortableTable data={this.allBookings}>
					<Column label="ID" handle="id" render={this._renderId} />
					<Column label="Name" handle="customerName" />
					<Column label="Email" handle="customerEmail" />
					<Column label="Order" handle="orderId" render={this._renderOrder} />
					<Column label="Date Booked" handle="dateBooked" render={this._renderDate} />
				</SortableTable>
			</div>
		);
	}

	_renderId (row) {
		return (
			<a href={`/bookings/booking/${row.id}`}>
				#{row.id}
			</a>
		);
	}

	_renderOrder (row) {
		return (
			<a href={`/admin/commerce/orders/${row.orderId}`}>
				#{row.orderId}
			</a>
		);
	}

	_renderDate (row, column) {
		return formatDate(
			row[column.handle],
			window.bookingsDateTimeFormat
		);
	}

}
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