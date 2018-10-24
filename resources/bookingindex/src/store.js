import Vuex from 'vuex';
import { get } from './helpers/fetch';

export default new Vuex.Store({

	state: {

		// Events
		// ---------------------------------------------------------------------

		events: {},
		sortedEventIds: [],

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

		// Bookings
		// ---------------------------------------------------------------------

		storeBookings (state, bookings) {
			const { byId, byEventId } = bookings.reduce((a, b) => {
				a.byId[b.id] = b;

				if (!a.byEventId.hasOwnProperty(b.eventId))
					a.byEventId[b.eventId] = [];

				a.byEventId[b.eventId].push(b.id);
			}, { byId: {}, byEventId: {} });

			state.bookings = {
				...state.bookings,
				...byId,
			};

			state.bookingsByEventId = {
				...state.bookingsByEventId,
				...byEventId,
			};
		}

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

		// Bookings
		// ---------------------------------------------------------------------

		async getBookings ({ commit }, { eventId }) {
			const bookings = await get('bookings/api/get-bookings', { eventId });
			commit('storeBookings', bookings);
		},

	},

});