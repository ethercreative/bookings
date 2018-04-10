import Vue from "vue";
import Vuex from "vuex";
import RecursionRule from "../models/RecursionRule";
import ExRule from "../models/ExRule";

Vue.use(Vuex);

const state = {
	baseRule: new RecursionRule(),
	exceptions: {},
	exceptionsSort: [],
};

const getters = {
	getExceptionById: (state) => (id) => {
		return state.exceptions[id];
	},
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
};

export default new Vuex.Store({
	state,
	getters,
	mutations,
	debug: process.env.NODE_ENV !== "production",
});