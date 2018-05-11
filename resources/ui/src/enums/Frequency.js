export default class Frequency {

	// Properties
	// =========================================================================

	// Properties: Static
	// -------------------------------------------------------------------------

	static Yearly = "YEARLY";
	static Monthly = "MONTHLY";
	static Weekly = "WEEKLY";
	static Daily = "DAILY";
	static Hourly = "HOURLY";
	static Minutely = "MINUTELY";

	// Functions
	// =========================================================================

	/**
	 * @return {{key: string, value: *}[]}
	 */
	static asKeyValueArray () {
		return Object.keys(this).map(key => ({ key, value: this[key] }));
	}

}