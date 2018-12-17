import Vuex from 'vuex';
import { get } from './helpers/fetch';
import formatDate from './helpers/formatDate';

export default new Vuex.Store({

	state: {

		// Events
		// ---------------------------------------------------------------------

		events: {},
		sortedEventIds: [],

		// Slots
		// ---------------------------------------------------------------------

		slotsByEventId: {},

		// Bookings
		// ---------------------------------------------------------------------

		bookings: {},
		bookingsByEventId: {},

	},

	mutations: {

		// Events
		// ---------------------------------------------------------------------

		storeEvents (state, events) {
			const eventsById = events.reduce((a, b) => {
				a[b.id] = b;
				return a;
			}, {});

			state.events = {
				...state.events,
				...eventsById,
			};
		},

		storeSortedEventIds (state, { clear, eventIds }) {
			if (clear) {
				state.sortedEventIds = eventIds;
				return;
			}

			state.sortedEventIds = {
				...state.sortedEventIds,
				...eventIds,
			};
		},

		// Slots
		// ---------------------------------------------------------------------

		storeSlotsByEventId (state, { eventId, slots }) {
			state.slotsByEventId[eventId] = slots;
		},

		// Bookings
		// ---------------------------------------------------------------------

		storeBooking (state, booking) {
			booking.dateBooked = new Date(booking.dateBooked);
			booking.dateCreated = new Date(booking.dateCreated);
			booking.dateUpdated = new Date(booking.dateUpdated);

			booking.bookedTickets = booking.bookedTickets.map(ticket => {
				ticket.startDate = new Date(ticket.startDate.date);
				return ticket;
			});

			state.bookings[booking.id] = booking;
		},

		storeBookings (state, bookings) {
			const { byId, byEventId } = bookings.reduce((a, b) => {
				b.dateBooked = new Date(b.dateBooked);

				a.byId[b.id] = b;

				if (!a.byEventId.hasOwnProperty(b.eventId))
					a.byEventId[b.eventId] = [];

				a.byEventId[b.eventId].push(b.id);

				return a;
			}, { byId: {}, byEventId: {} });

			// state.bookings = {
			// 	...state.bookings,
			// 	...byId,
			// };
			//
			// state.bookingsByEventId = {
			// 	...state.bookingsByEventId,
			// 	...byEventId,
			// };

			state.bookings = byId;
			state.bookingsByEventId = byEventId;
		},

	},

	actions: {

		// Events
		// ---------------------------------------------------------------------

		async getEvent ({ commit }, { eventId }) {
			const event = await get('bookings/api/get-event', { eventId });
			commit('storeEvents', event);
			commit('storeSortedEventIds', {
				clear: true,
				eventIds: [event.id],
			});
		},

		async getEvents ({ commit }) {
			const events = await get('bookings/api/get-events');
			commit('storeEvents', events);
			commit('storeSortedEventIds', {
				clear: true,
				eventIds: events.map(e => e.id),
			});
		},

		async getSlotsForEvent ({ commit }, { eventId }) {
			const { slots: rawSlots } = await get('bookings/api/get-event-slots', { eventId });

			const slots = rawSlots.map(({ date }) => ({
				label: formatDate(new Date(date), window.bookingsDateTimeFormat),
				value: date,
			}));

			commit('storeSlotsByEventId', { eventId, slots });
		},

		// Bookings
		// ---------------------------------------------------------------------

		async getBooking({ commit }, { bookingId }) {
			const booking = await get('bookings/api/get-booking', { bookingId });
			commit('storeBooking', booking);
		},

		async getBookings ({ commit }, { eventId, slot = null, query = null }) {
			const bookings = await get('bookings/api/get-bookings', {
				eventId,
				slot,
				query,
			});
			commit('storeBookings', bookings);
		},

	},

});