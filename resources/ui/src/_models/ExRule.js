import RRule from "./RRule";
import constructModel from "../_helpers/constructModel";

export default class ExRule extends RRule {

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
		constructModel(this, def, overwriteId);
	}

}