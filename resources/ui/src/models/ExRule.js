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

}