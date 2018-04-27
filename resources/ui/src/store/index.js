import Vue from "vue";
import Vuex from "vuex";
import API from "../API";
import RecursionRule from "../models/RecursionRule";
import ExRule from "../models/ExRule";

Vue.use(Vuex);

// API
// =========================================================================

/**
 * Refreshes the calendar data from the API
 *
 * @param commit
 * @param state
 * @return {Promise<void>}
 */
async function refreshCalendar (commit, state) {
	const body = {
		baseRule: state.baseRule.convertToDataObject(),
		exceptions: Object.values(state.exceptions).map(r => r.convertToDataObject()),
	};

	try {
		const res = await API.postActionRequest("bookings/api/get-calendar", body);

		const slots = res.slots.reduce((slots, slot) => {
			const d = new Date(slot.date);

			// Convert from UTC to local time
			// (dropping seconds & milliseconds, since we're not using them)
			slot.date = new Date(Date.UTC(
				d.getFullYear(),
				d.getMonth(),
				d.getDate(),
				d.getHours(),
				d.getMinutes(),
				0,
				0
			));

			slot.day = slot.date.getDay();
			slot.hour = slot.date.getHours();
			slot.minute = slot.date.getMinutes();

			if (slot.hour === 0) {
				slot.hour = 24;
				slot.day--;

				if (slot.day < 1)
					slot.date.setDate(slot.date.getDate() - 1);

				slot.day = slot.date.getDay();
			}

			const month = slot.date.getMonth() + 1
				, date = slot.date.getDate()
				, key = slot.date.getTime();

			if (!slots.hasOwnProperty(month))
				slots[month] = { all: {} };

			if (!slots[month].hasOwnProperty(date))
				slots[month][date] = [];

			slots[month].all[key] = slot;
			slots[month][date].push(key);

			return slots;
		}, {});

		commit("refreshComputedSlots", slots);
	} catch (e) {
		console.error(e);
	}
}

// State
// =========================================================================

const state = {
	baseRule: new RecursionRule(),
	exceptions: {},
	exceptionsSort: [],

	computedSlots: {},
};

const getters = {
	getExceptionById: (state) => (id) => {
		return state.exceptions[id];
	},
};

const actions = {

	async updateRule ({ commit, state }, payload) {
		commit("updateRule", payload);
		await refreshCalendar(commit, state);
	},

	async addException ({ commit, state }) {
		commit("addException");
		await refreshCalendar(commit, state);
	},

	async updateExceptionsSort ({ commit, state }, payload) {
		commit("updateExceptionsSort",  payload);
		await refreshCalendar(commit, state);
	},

	async duplicateExceptionById ({ commit, state }, payload) {
		commit("duplicateExceptionById", payload);
		await refreshCalendar(commit, state);
	},

	async deleteExceptionById ({ commit, state }, payload) {
		commit("deleteExceptionById", payload);
		await refreshCalendar(commit, state);
	}

};

const mutations = {

	/**
	 * Updates the given rule
	 *
	 * @param state
	 * @param {RecursionRule|ExRule} nextRule
	 */
	updateRule (state, nextRule) {
		if (nextRule.constructor === RecursionRule) {
			state.baseRule = nextRule;
			return;
		}

		state.exceptions[nextRule.id] = nextRule;
	},

	/**
	 * Creates a new exception and adds it to the bottom of the list
	 *
	 * @param state
	 */
	addException (state) {
		const newExRule = new ExRule();
		state.exceptions[newExRule.id] = newExRule;
		state.exceptionsSort.push(newExRule.id);
	},

	/**
	 * Updates the order of the exceptions
	 *
	 * @param state
	 * @param exceptionsSort
	 */
	updateExceptionsSort (state, exceptionsSort) {
		state.exceptionsSort = exceptionsSort;
	},

	/**
	 * Duplicates the exception (by the given ID) appends it to the list
	 *
	 * @param state
	 * @param id
	 */
	duplicateExceptionById (state, id) {
		const ex = state.exceptions[id];
		const next = new ExRule(ex, true);

		state.exceptions[next.id] = next;
		state.exceptionsSort.push(next.id);
	},

	/**
	 * Deletes the exception for the given ID
	 *
	 * @param state
	 * @param id
	 */
	deleteExceptionById (state, id) {
		const nextExceptions = { ...state.exceptions }
			, nextSort = [ ...state.exceptionsSort ];

		delete nextExceptions[id];
		nextSort.splice(nextSort.indexOf(id), 1);

		state.exceptions = nextExceptions;
		state.exceptionsSort = nextSort;
	},

	/**
	 * Sets the computed slots
	 *
	 * @param state
	 * @param slots
	 */
	refreshComputedSlots (state, slots) {
		state.computedSlots = slots;
	},
};

export default new Vuex.Store({
	state,
	getters,
	actions,
	mutations,
	debug: process.env.NODE_ENV !== "production",
});