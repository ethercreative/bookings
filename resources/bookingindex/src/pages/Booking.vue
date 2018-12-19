<script type="text/jsx">
import Vue from 'vue';
import Component from 'vue-class-component';
import Header from '../components/BookingsHeader';
import Button from '../components/BookingsButton';
import Select from '../components/BookingsSelect';
import Modal from '../components/Modal';
import TicketFields from '../components/TicketFields';
import MiniCalendar from '../components/MiniCalendar';
import formatDate from '../helpers/formatDate';
import { post } from '../helpers/fetch';

@Component
export default class Booking extends Vue {

	// Properties
	// =========================================================================

	busy = false;

	editModalOpen = false;
	activeTicket = null;
	availability = null;
	activeDay = null;
	slots = [];

	selectedNewSlot = null;

	error = null;

	// Getters
	// =========================================================================

	get booking () {
		return this.$store.state.bookings[this.$route.params.bookingId];
	}

	// Vue
	// =========================================================================

	async mounted () {
		const { bookingId } = this.$route.params;
		await this.$store.dispatch('getBooking', { bookingId });
	}

	// Actions
	// =========================================================================

	async getAvailability () {
		const start = new Date(this.activeTicket.startDate.getTime())
			, end   = new Date(this.activeTicket.startDate.getTime());

		start.setDate(0);
		end.setMonth(end.getMonth() + 6);
		end.setDate(0);

		this.busy = true;
		this.availability = await post('bookings/availability', {
			eventId: this.booking.eventId,
			start,
			end,
			group: 'day',
		});
		this.busy = false;
	}

	async getSlots () {
		const start = new Date(this.activeDay)
			, end   = new Date(this.activeDay);

		start.setHours(0);
		start.setMinutes(0);
		end.setHours(23);
		end.setMinutes(59);

		this.busy = true;
		const slots = await post('bookings/availability', {
			eventId: this.booking.eventId,
			start,
			end,
		});

		const bookingSlots = this.booking.bookedTickets.reduce((a, b) => {
			b.slots.forEach(({ date: { date } }) => {
				if (a.indexOf(date) === -1)
					a.push(date.replace('.000000', ''));
			});

			return a;
		}, []);

		this.slots = Object.keys(slots).reduce((a, b) => {
			const date = new Date(b);
			let left = slots[b];

			if (bookingSlots.indexOf(b) > -1)
				left++;

			const disabled = left === 0;

			let suffix = ' ';
			if (disabled) suffix += '(Fully Booked)';
			else suffix += `(${left} slot${left === 1 ? '' : 's'} left)`;

			a.push({
				label: formatDate(date, window.bookingsDateTimeFormat) + suffix,
				value: formatDate(date, 'Y-m-d H:i:s'),
				disabled,
			});
			return a;
		}, []);

		this.selectedNewSlot = new Date(this.slots[0].value);
		this.busy = false;
	}

	async updateTicket () {
		this.busy = true;
		this.error = null;

		try {
			const { success, errors } = await post('bookings/api/update-booking', {
				bookingId: this.booking.id,
				slot: this.selectedNewSlot,
			});

			if (success === false) {
				this.busy = false;
				let err = Object.values(errors);
				if (Array.isArray(err)) err = err[0];
				this.error = err;
				return;
			}
		} catch (e) {
			this.busy = false;
			return;
		}

		await this.$store.dispatch('getBooking', {
			bookingId: this.booking.id,
		});
		this.busy = false;

		this.editModalOpen = false;
		this.selectedNewSlot = null;
	}

	// Events
	// =========================================================================

	async onEditClick (e) {
		e.preventDefault();

		this.activeTicket = this.booking.bookedTickets[0];
		this.editModalOpen = true;

		this.availability = null;
		await this.getAvailability();
	}

	onRequestCloseEditModal () {
		this.editModalOpen = false;
	}

	async onDayClick (day) {
		this.activeDay = day;
		this.getSlots();
	}

	onSlotSelectChange (e) {
		this.selectedNewSlot = new Date(e.target.value);
	}

	onUpdateClick (e) {
		e.preventDefault();
		this.updateTicket();
	}

	// Render
	// =========================================================================

	render () {
		// TODO: Show loading screen
		if (this.booking === undefined || this.booking.shortNumber === undefined)
			return <span className="spinner"/>;

		return (
			<div class={this.$style.wrap}>
				<Header
					back="Back to event"
					to={`/events/${this.booking.eventId}`}

					heading={`#${this.booking.shortNumber}`}
				/>

				<div class={this.$style.content}>
					{this._renderOrderDetails()}
					{this._renderCustomerDetails()}

					<header class={this.$style.ticketsHeader}>
						<p>Tickets</p>


						<Button
							label="Edit"
							small
							onClick={this.onEditClick}
						/>
					</header>

					<ul class={this.$style.tickets}>
						{this.booking.bookedTickets.map(this._renderTicket)}
					</ul>

					<div class={this.$style.customFields}>
						<p>Custom Fields</p>
						<TicketFields
							bookingId={this.booking.id}
						/>
					</div>
				</div>

				{this._renderEditModal()}
			</div>
		);
	}

	_renderOrderDetails () {
		return (
			<div class={[this.$style.block, this.$style.order]}>
				<h2>Order Details</h2>

				<table class="collapsible">
					<tbody>
					<tr>
						<th>Date of Order</th>
						<td>
							{formatDate(
								this.booking.dateBooked,
								window.bookingsDateTimeFormat
							)}
						</td>
					</tr>
					<tr>
						<th>Customer</th>
						<td>
							<a href={`mailto:${this.booking.customerEmail}`}>
								{this.booking.customerEmail}
							</a>
						</td>
					</tr>
					<tr>
						<th>Linked Order</th>
						<td>
							<a href={`/admin/commerce/orders/${this.booking.orderId}`}>
								#{this.booking.orderId}
							</a>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
		);
	}

	_renderCustomerDetails () {
		const a = this.booking.order.billingAddress;

		return (
			<div class={[this.$style.block, this.$style.customer]}>
				<h2>Customer Details</h2>

				{a && (
					<p>
						{a.attention}{a.attention && <br/>}
						{a.businessName}{a.businessName && <br/>}
						{a.fullName}{a.fullName && <br/>}
						{a.address1}{a.address1 && <br/>}
						{a.address2}{a.address2 && <br/>}
						{a.city}{a.city && <br/>}
						{a.stateText}{a.stateText && <br/>}
						{a.countryText}{a.countryText && <br/>}
						{a.zipCode}{a.zipCode && <br/>}
					</p>
				)}
				{a && (a.phone || a.alternativePhone) && (
					<p>
						{a.phone}{a.phone && <br/>}
						{a.alternativePhone}
					</p>
				)}
			</div>
		);
	}

	_renderTicket (ticket) {
		return (
			<li key={ticket.id}>
				<span class={this.$style.slots}>
					{ticket.slots.length}
				</span>

				<span class={this.$style.title}>
					{ticket.productName ? ticket.productName + ' - ' : ''}
					{ticket.ticketName}
				</span>

				{formatDate(
					ticket.startDate,
					window.bookingsDateTimeFormat
				)}
			</li>
		);
	}

	_renderEditModal () {
		if (this.activeTicket === null || this.availability === null)
			return null;

		return (
			<Modal
				isOpen={this.editModalOpen && this.activeTicket !== null}
				whenRequestClose={this.onRequestCloseEditModal}
			>
				<div class={[
					this.$style.modalBusy,
					{ [this.$style.show]: this.busy }
				]}>
					<span class="spinner"/>
				</div>

				<h1 class={this.$style.modalHeading}>
					Edit this booking
				</h1>

				<hr/>

				<div class={this.$style.modalCalendar}>
					<MiniCalendar
						activeDate={this.activeTicket.startDate}
						activeDay={this.activeDay}
						availability={this.availability}
						whenDayClick={this.onDayClick}
					/>
				</div>

				<hr/>

				{this.activeDay && this.slots.length > 0 ? (
					<div>
						{this.error && (
							<p class={this.$style.modalError}>{this.error}</p>
						)}

						<Select
							options={this.slots}
							class={this.$style.modalSelect}
							onChange={this.onSlotSelectChange}
						/>

						<Button
							label="Update Ticket"
							wide
							onClick={this.onUpdateClick}
						/>
					</div>
				) : (
					<p class={this.$style.modalEmpty}>
						Please select a date
					</p>
				)}
			</Modal>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";

	.content {
		display: grid;
		grid-template-columns: repeat(12, 1fr);
		grid-gap: @spacer;
		padding: @spacer;

		table {
			th {
				color: #7C858B;
			}

			td:not(:first-child) {
				padding-left: 14px;
			}

			tr:not(:last-child) {
				th,
				td {
					padding-bottom: 10px;
				}
			}
		}
	}

	.block {
		padding: @spacer;

		border: 1px solid @border;
		border-radius: 4px;

		h2 {
			margin-bottom: @spacer/2 !important;

			font-weight: 300;
			font-size: 24px;
			line-height: 1;
		}
	}

	.order {
		grid-column: span 7;
	}

	.customer {
		grid-column: span 5;
	}

	.ticketsHeader {
		display: flex;
		align-items: flex-end;
		justify-content: space-between;
		grid-column: span 12;

		p {
			margin-bottom: 0;
		}
	}

	.tickets {
		grid-column: span 12;
		list-style: none;

		li {
			display: flex;
			align-items: center;
			margin-bottom: 10px;
			padding: @spacer/2;

			background: #F1F5F8;
			border: 1px solid #D9DDE2;
			border-radius: 3px;
		}

		button {
			margin-left: 10px !important;
		}
	}

	.customFields {
		grid-column: span 12;
	}

	.slots {
		display: inline-block;
		width: 35px;
		margin-right: 15px;
		padding: 3px 0;

		color: #fff;
		line-height: normal;
		text-align: center;

		background: #AEBEC9;
		border-radius: 12px;
	}

	.title {
		display: inline-block;
		margin-right: 15px;
		flex-grow: 1;
	}

	.modalHeading {
		padding: @spacer @spacer 0;
	}

	.modalCalendar {
		text-align: center;

		> div {
			display: inline-block;
			margin: 0 !important;
			float: none !important;

			background-color: transparent;
			box-shadow: none !important;

			transform: scale(1.2);
		}
	}

	.modalSelect {
		margin-bottom: -@spacer/2;
	}

	.modalBusy {
		position: absolute;
		z-index: 3;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;

		display: flex;
		align-items: center;
		justify-content: center;

		background-color: rgba(255, 255, 255, 0.7);

		opacity: 0;
		pointer-events: none;
		transition: opacity 0.15s ease;

		&.show {
			opacity: 1;
			pointer-events: all;
		}
	}

	.modalEmpty {
		padding: 0 @spacer @spacer;

		text-align: center;

		opacity: 0.25;
	}

	.modalError {
		margin: 0 30px -20px;
		color: @craft-primary;
	}
</style>