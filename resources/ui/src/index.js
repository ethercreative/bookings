import "preact-cli/lib/lib/webpack/polyfills";
import { Component } from "preact";
import habitat from "preact-habitat";
import store from "./store";
import Field from "./Field/Field";
import RRule from "./_models/RRule";
import ExRule from "./_models/ExRule";
import BookableType from "./_enums/BookableType";
import { refreshCalendar } from "./API";
import jQuizzle from "./_helpers/jQuizzle";

class App extends Component {

	constructor (props) {
		super(props);

		const state = {
			...props,
			slots: {},
			exceptions: {},
		};
		delete state.children;

		state.settings.bookableType = state.settings.bookableType || BookableType.FIXED;
		state.settings.baseRule = new RRule(state.settings.baseRule);
		state.settings.exceptions = state.settings.exceptions.map(e => new ExRule(e));

		store.subscribe(App.onStateChange);

		store("set", state);
	}

	// Events
	// =========================================================================

	static _previousState = "";

	/**
	 * TODO: It would be better to manually trigger the refresh when needed
	 * @param state
	 */
	static onStateChange (state) {
		if (process.env.NODE_ENV === "development")
			console.log(state);

		// Compare the new state (without props we want to ignore) w/ the previous one
		let nextState = { ...state };
		delete nextState.enabled;
		delete nextState.slots;
		delete nextState.exceptions;
		nextState = JSON.stringify(nextState);

		// If the new state & previous state match, do nothing
		if (nextState === App._previousState)
			return;

		App._previousState = nextState;

		// Else refresh the calendar
		refreshCalendar();
	}

	// Render
	// =========================================================================

	render () {
		return <Field />;
	}
}

// Looks like we'll just have to wait for jQuery
// (at least while we're using Garnish / Craft.js)
jQuizzle(() => {
	habitat(App).render({
		selector: "craft-bookings",
		clean: true,
	});
});
