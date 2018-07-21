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
	 * @type {Date}
	 */
	start = new Date();

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

		def && Object.keys(def).map(key => {
			if (!this.hasOwnProperty(key))
				return;

			if (key === "id" && overwriteId)
				return;

			let value = def[key];

			if (~["start", "until"].indexOf(key)) {
				if (!(value instanceof Date))
					value = new Date(value);
				value.setSeconds(0);
				value.setMilliseconds(0);
			}

			this[key] = value;
		});
	}

}