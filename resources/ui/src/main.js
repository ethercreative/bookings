import Vue from "vue";

Vue.config.productionTip = false;

/**
 * ```js
 * new window.__BookingsUI("field", "#namespacedId");
 * ```
 *
 * @param {string} section
 * @param {string=} id
 * @constructor
 */
function BookingsUI (section, id = "#app") {

	const onImport = App => {
		new Vue({
			render: h => h(App.default)
		}).$mount(id);
	};

	switch (section) {
		case "dev":
			import("./App.vue").then(onImport);
			break;
		default:
			throw new Error(`Unknown Bookings UI section: ${section}`);
	}

}

if (process.env.NODE_ENV === "development")
	new BookingsUI("dev");

window.__BookingsUI = BookingsUI;