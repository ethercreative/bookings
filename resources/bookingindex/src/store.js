import Vuex from 'vuex';
import { get } from './helpers/fetch';

export default new Vuex.Store({

	state: {
		events: {},
		sortedEventIds: [],
	},

	mutations: {

		storeEvents (state, events) {
			const eventsById = events.reduce((a, b) => {
				a[b.id] = b;
				return a;
			}, {});

			state.events = {
				...this.events,
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

	},

	actions: {

		async getEvents ({ commit }) {
			const events = await get('bookings/api/get-events');
			commit('storeEvents', events);
			commit('storeSortedEventIds', {
				clear: true,
				eventIds: events.map(e => e.id),
			});
		},

	},

});