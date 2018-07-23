import uuid from "../_helpers/uuid";
import Frequency from "../_enums/Frequency";

export default class RRule {

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
	 * @type {{date:Date, timezone:String}}
	 */
	start = {
		date: new Date(),
		timezone: "",
	};

	/**
	 * @type {number}
	 */
	duration = 1;

	/**
	 * @type {Number}
	 */
	interval = 0;

	/**
	 * @type {String}
	 */
	repeats = "count";

	/**
	 * @type {Number|null}
	 */
	count = 1;

	/**
	 * @type {{date:Date, timezone:String}|null}
	 */
	until = {
		date: new Date(),
		timezone: "",
	};

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

		def && Object.keys(def).map(key => {
			if (!this.hasOwnProperty(key))
				return;

			if (key === "id" && overwriteId)
				return;

			let value = def[key];

			if (~["start", "until"].indexOf(key)) {
				if (!(value.date instanceof Date))
					value.date = new Date(value.date);
				value.date.setSeconds(0);
				value.date.setMilliseconds(0);
			}

			this[key] = value;
		});
	}

}