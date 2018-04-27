import Frequency from "../const/Frequency";
import uuid from "../helpers/uuid";

export default class RecursionRule {

	// Properties
	// =========================================================================

	// Properties: Instance
	// -------------------------------------------------------------------------

	/**
	 * @type {String|null}
	 */
	id = null;

	/**
	 * @type {String}
	 */
	frequency = Frequency.Hourly;

	/**
	 * @type {Date}
	 */
	start = new Date();

	/**
	 * @type {Number}
	 */
	interval = 1;

	/**
	 * @type {String}
	 */
	repeats = "count";

	/**
	 * @type {Number|null}
	 */
	count = 1;

	/**
	 * @type {Date|null}
	 */
	until = new Date();

	/**
	 * @type {Number[]|null}
	 */
	byMonth = [];

	/**
	 * @type {Number[]|null}
	 */
	byWeekNumber = [];

	/**
	 * @type {Number[]|null}
	 */
	byYearDay = [];

	/**
	 * @type {Number[]|null}
	 */
	byMonthDay = [];

	/**
	 * @type {String[]|null}
	 */
	byDay = [];

	/**
	 * @type {Number[]|null}
	 */
	byHour = [];

	/**
	 * @type {Number[]|null}
	 */
	byMinute = [];

	/**
	 * @type {Number[]|null}
	 */
	bySetPosition = [];

	// Constructor
	// =========================================================================

	constructor (def = {}, overwriteId = false) {
		this.id = uuid();

		Object.keys(def).map(key => {
			if (!this.hasOwnProperty(key))
				return;

			if (key === "id" && overwriteId)
				return;

			let value = def[key];

			if (key in ["start", "until"])
				value = new Date(+value);

			this[key] = value;
		});
	}

	// Helpers
	// =========================================================================

	/**
	 * Returns the class as a plain JS object ready of Vue's data()
	 *
	 * @return {{}}
	 */
	convertToDataObject () {
		return Object.keys(this).reduce((obj, key) => {
			if (key !== "id")
				obj[key] = this[key];

			return obj;
		}, {});
	}

	/**
	 * Converts the class to a plain JS object ready for sending to the server
	 *
	 * @return {{}}
	 */
	convertToRRuleObject () {
		const data = this.convertToDataObject();

		switch (data.repeats) {
			case "until":
				delete data.count;
				break;
			case "count":
				delete data.until;
				break;
			default:
				delete data.count;
				delete data.until;
		}

		return data;
	}

}