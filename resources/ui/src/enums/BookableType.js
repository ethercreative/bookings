export default class BookableType {

	// Properties
	// =========================================================================

	// Properties: Static
	// -------------------------------------------------------------------------

	static FIXED = "fixed";
	static FLEXIBLE = "flexible";

	// Functions
	// =========================================================================

	/**
	 * @return {{key: string, value: *}[]}
	 */
	static asKeyValueArray () {
		return Object.keys(this).map(key => ({ key, value: this[key] }));
	}

}