<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import Header from '../components/BookingsHeader';
import Button from '../components/BookingsButton';
import Select from '../components/BookingsSelect';
import Modal from '../components/Modal';
import MiniCalendar from '../components/MiniCalendar';
import formatDate from '../helpers/formatDate';

@Component
export default class Booking extends Vue {

	// Properties
	// =========================================================================

	busy = false;

	editModalOpen = false;
	activeTicket = null;

	// Getters
	// =========================================================================

	get booking () {
		return this.$store.state.bookings[this.$route.params.bookingId];
	}

	// Vue
	// =========================================================================

	async mounted () {
		const { bookingId } = this.$route.params;

		this.busy = true;

		await this.$store.dispatch('getBooking', { bookingId });

		this.busy = false;
	}

	// Events
	// =========================================================================

	onEditClick (ticket, e) {
		e.preventDefault();

		this.activeTicket = ticket;
		this.editModalOpen = true;
	}

	onRequestCloseEditModal () {
		this.editModalOpen = false;
	}

	// Render
	// =========================================================================

	render () {
		// TODO: Show loading screen
		if (this.booking === undefined || this.booking.shortNumber === undefined)
			return null;

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

					<ul class={this.$style.tickets}>
						<li>Tickets</li>
						{this.booking.bookedTickets.map(this._renderTicket)}
					</ul>
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
				{(a.phone || a.alternativePhone) && (
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

				<Button
					label="Edit"
					small
					onClick={this.onEditClick.bind(this, ticket)}
				/>
			</li>
		);
	}

	_renderEditModal () {
		return (
			<Modal
				isOpen={this.editModalOpen}
				whenRequestClose={this.onRequestCloseEditModal}
			>
				<h1 class={this.$style.modalHeading}>
					Edit this ticket
				</h1>

				<hr/>

				<div class={this.$style.modalCalendar}>
					<MiniCalendar
						activeDate={this.booking.dateBooked}
					/>
				</div>

				<hr/>

				<Select
					options={[]}
				/>

				<Button label="Update Ticket" wide />
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

	.tickets {
		grid-column: span 12;
		list-style: none;

		li:first-child {
			margin-bottom: @spacer/2;
		}

		li:not(:first-child) {
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

			box-shadow: none !important;

			transform: scale(1.2);
		}
	}
</style>