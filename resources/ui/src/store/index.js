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
	// TODO: Add validation to RRules when converting
	// (don't refresh if any rule is invalid)

	const body = {
		baseRule: state.baseRule.convertToRRuleObject(),
		exceptions: Object.values(state.exceptions).map(r => r.convertToRRuleObject()),
	};

	try {
		const res = await API.postActionRequest("bookings/api/get-calendar", body);

		const slots = formatSlotsForStorage(res.slots);
		const exceptions = formatSlotsForStorage(res.exceptions);

		commit("refreshComputedSlots", { slots, exceptions, duration: res.duration });
	} catch (e) {
		console.error(e);
	}
}

// Helpers
// =========================================================================

/**
 * Parses the returned slots and formats them for storage & later use
 *
 * @param slots
 * @return {*}
 */
function formatSlotsForStorage (slots) {
	return slots.reduce((slots, slot) => {
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

		const year = slot.date.getFullYear()
			, month = slot.date.getMonth() + 1
			, date = slot.date.getDate()
			, key = slot.date.getTime();

		if (!slots.hasOwnProperty(year))
			slots[year] = {};

		if (!slots[year].hasOwnProperty(month))
			slots[year][month] = { all: {} };

		if (!slots[year][month].hasOwnProperty(date))
			slots[year][month][date] = [];

		slots[year][month].all[key] = slot;
		slots[year][month][date].push(key);

		return slots;
	}, {});
}

// State
// =========================================================================

const state = {
	baseRule: new RecursionRule(),
	exceptions: {},
	exceptionsSort: [],

	computedSlots: {},
	computedExceptions: {},
	slotDuration: 1,
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
	 */
	refreshComputedSlots (state, { slots, exceptions, duration }) {
		state.computedSlots = slots;
		state.computedExceptions = exceptions;
		state.slotDuration = duration;
	},
};

export default new Vuex.Store({
	state,
	getters,
	actions,
	mutations,
	debug: process.env.NODE_ENV !== "production",
});