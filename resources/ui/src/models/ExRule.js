import RecursionRule from "./RecursionRule";

export default class ExRule extends RecursionRule {

	// Properties
	// =========================================================================

	// Properties: Instance
	// -------------------------------------------------------------------------

	/**
	 * @type {boolean}
	 */
	bookable = false;

	// Constructor
	// =========================================================================

	constructor (def = {}, overwriteId = false) {
		super();

		def && Object.keys(def).map(key => {
			if (!this.hasOwnProperty(key))
				return;

			if (key === "id" && overwriteId)
				return;

			let value = def[key];

			if (~["start", "until"].indexOf(key)) {
				if (!(value instanceof Date))
					value = new Date(typeof value === "string" ? value : value.date + "Z");
				value.setSeconds(0);
				value.setMilliseconds(0);
			}

			this[key] = value;
		});
	}

}