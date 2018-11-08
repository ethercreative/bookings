<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import Header from '../components/BookingsHeader';
import Select from '../components/BookingsSelect';
import Search from '../components/Search';
import Button from '../components/BookingsButton';
import { SortableTable, Column } from '../components/SortableTable';
import formatDate from '../helpers/formatDate';
import MiniCalendar from '../components/MiniCalendar';

@Component
export default class Event extends Vue {

	// Properties
	// =========================================================================

	busy = false;
	activeDate = new Date();

	// Getters
	// =========================================================================

	get event () {
		return this.$store.state.events[this.$route.params.eventId];
	}

	get allBookings () {
		const bookingIds = this.$store.state.bookingsByEventId[this.$route.params.eventId];

		if (!bookingIds)
			return [];

		return bookingIds.map(id => this.$store.state.bookings[id]);
	}

	// Vue
	// =========================================================================

	async mounted () {
		const { eventId } = this.$route.params;

		this.busy = true;

		await this.$store.dispatch('getEvent', { eventId });
		await this.$store.dispatch('getBookings', { eventId });

		this.busy = false;
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
				>
					<div class={this.$style.calendar}>
						<MiniCalendar
							activeDate={this.activeDate}
						/>
					</div>
				</Header>

				<div class={this.$style.filter}>
					<Select
						onChange={this.onSelectChange}
						options={[
							{ label: 'All Slots', value: '*' },
							{ label: 'All Slots for 22nd October', value: '*:2018-10-1' },
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
					<Column
						label={this._renderSettingsButton}
						handle="_settings"
						styles={{padding:0, width:'30px'}}
					/>
				</SortableTable>
			</div>
		);
	}

	_renderId (row) {
		return (
			<router-link to={`/bookings/${row.id}`}>
				#{row.id}
			</router-link>
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

	_renderSettingsButton () {
		return (
			<button
				type="button"
				class={this.$style.settings}
				title="Customise Columns"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12">
					<path
						fill="#75889E"
						d="M1395.58034,342.585 C1395.60501,342.393 1395.62351,342.198 1395.62351,342 C1395.62351,341.802 1395.60501,341.607 1395.58034,341.415 L1396.88461,340.422 C1397.00177,340.332 1397.03569,340.17 1396.95861,340.038 L1395.72526,337.959 C1395.64818,337.83 1395.48784,337.776 1395.34909,337.83 L1393.81357,338.433 C1393.49599,338.196 1393.14757,337.995 1392.77139,337.842 L1392.54014,336.252 C1392.51239,336.111 1392.38597,336 1392.23181,336 L1389.76511,336 C1389.61094,336 1389.48452,336.111 1389.45986,336.252 L1389.22861,337.842 C1388.85243,337.995 1388.50401,338.193 1388.18643,338.433 L1386.65091,337.83 C1386.51216,337.779 1386.35182,337.83 1386.27474,337.959 L1385.04139,340.038 C1384.96431,340.167 1384.99823,340.329 1385.11539,340.422 L1386.41657,341.415 C1386.39191,341.607 1386.37341,341.802 1386.37341,342 C1386.37341,342.198 1386.39191,342.393 1386.41657,342.585 L1385.11539,343.578 C1384.99823,343.668 1384.96431,343.83 1385.04139,343.962 L1386.27474,346.041 C1386.35182,346.17 1386.51216,346.224 1386.65091,346.17 L1388.18643,345.567 C1388.50401,345.804 1388.85243,346.005 1389.22861,346.158 L1389.45986,347.748 C1389.48452,347.889 1389.61094,348 1389.76511,348 L1392.23181,348 C1392.38597,348 1392.51239,347.889 1392.53706,347.748 L1392.76831,346.158 C1393.14448,346.005 1393.4929,345.807 1393.81049,345.567 L1395.34601,346.17 C1395.48476,346.221 1395.64509,346.17 1395.72218,346.041 L1396.95552,343.962 C1397.03261,343.833 1396.99869,343.671 1396.88152,343.578 L1395.58034,342.585 Z M1390.99846,344.1 C1389.8052,344.1 1388.8401,343.161 1388.8401,342 C1388.8401,340.839 1389.8052,339.9 1390.99846,339.9 C1392.19172,339.9 1393.15682,340.839 1393.15682,342 C1393.15682,343.161 1392.19172,344.1 1390.99846,344.1 Z"
						transform="translate(-1385 -336)"
					/>
				</svg>
			</button>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.calendar {
		grid-column: span 6;
	}

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

	.settings {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100%;
		padding: 7px 0;

		appearance: none;
		background: none;
		border: none;
		border-radius: 0;
		cursor: pointer;

		opacity: 0.5;
		transition: opacity 0.15s ease;

		&:hover {
			opacity: 1;
		}

		svg {
			margin-top: 2px;
			vertical-align: middle;
		}
	}
</style>