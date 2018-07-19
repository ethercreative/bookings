export default class BaseEnum {

	/**
	 * @return {{key: string, value: *}[]}
	 */
	static asKeyValueArray () {
		return Object.keys(this).map(key => ({ key, value: this[key] }));
	}

}