import Frequency from "../_enums/Frequency";
import constructModel from "../_helpers/constructModel";

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
		constructModel(this, def, overwriteId);
	}

}