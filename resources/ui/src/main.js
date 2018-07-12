import "whatwg-fetch";
import Vue from "vue";
import PortalVue from "portal-vue";
import store from "./store";

Vue.config.productionTip = false;
Vue.use(PortalVue);

/**
 * ```js
 * new window.__BookingsUI("field", "#namespacedId", {});
 * ```
 *
 * @param {string} section
 * @param {string=} id
 * @param {Object=} options
 * @constructor
 */
function BookingsUI (section, id = "#app", options = null) {

	const onImport = App => {
		const vm = new Vue({
			render: h => h(App.default),
			data: { options },
			store,
		}).$mount();

		// Mount as a child of the parent (not replacing it)
		document.querySelector(id).appendChild(vm.$el);
	};

	switch (section) {
		case "dev":
		case "field":
			import("./Field/index.vue").then(onImport);
			break;
		default:
			throw new Error(`Unknown Bookings UI section: ${section}`);
	}

}

if (process.env.NODE_ENV === "development") {
	new BookingsUI(
		"dev",
		"#app",
		{
			handle: "fieldHandle",
			value: {
				"enabled": true,
				"settings":{"baseRule":{"frequency":"MINUTELY","start":{"date":"2018-07-12 01:00:00.000000","timezone_type":3,"timezone":"UTC"},"duration":"10","interval":0,"repeats":"count","count":"5","until":{"date":"2018-07-12 01:00:00.000000","timezone_type":3,"timezone":"UTC"},"byMonth":[],"byWeekNumber":[],"byYearDay":[],"byMonthDay":[],"byDay":[],"byHour":[],"byMinute":[],"bySetPosition":[]},"exceptions":[{"bookable":false,"repeats":"count","frequency":"MINUTELY","start":"2018-05-15T13:10:00.000Z","duration":1,"interval":"10","count":"2","until":"2018-05-15T16:46:00.000Z","byMonth":[],"byWeekNumber":[],"byYearDay":[],"byMonthDay":[],"byDay":[],"byHour":[],"byMinute":[],"bySetPosition":[]}],"bookableType":"fixed"}
			}
		}
	);
}


window.__BookingsUI = BookingsUI;