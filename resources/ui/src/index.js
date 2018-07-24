import "preact-cli/lib/lib/webpack/polyfills";
import { Component } from "preact";
import habitat from "preact-habitat";
import store from "./store";
import Field from "./Field/Field";
import RRule from "./_models/RRule";
import ExRule from "./_models/ExRule";
import BookableType from "./_enums/BookableType";

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

		if (process.env.NODE_ENV === "development") {
			store.subscribe(s => console.log(s));
		}

		store("set", state);
	}

	render () {
		return <Field />;
	}
}

habitat(App).render({
	selector: "craft-bookings",
	clean: true,
});
