/**
 * It's possible for our bundle to load & execute before jQuery has loaded.
 * This let's us wait for jQuery before we use it.
 *
 * @param cb - The function to execute once jQuery is ready.
 */
export default function jQuizzle (cb) {
	if (window.$) {
		cb(window.$);
		return;
	}

	Object.defineProperty(window, "$", {
		configurable: true,
		enumerable: true,
		writeable: true,
		get: function () {
			return this._$;
		},
		set: function (val) {
			this._$ = val;
			cb(this._$);
		}
	});
}