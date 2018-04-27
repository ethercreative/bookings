/* global Craft */

export default {
	async postActionRequest (action, data) {
		const url = typeof Craft !== "undefined"
			? Craft.actionUrl + "/"
			: "https://dev.craft3/index.php?p=actions/";

		const opts = {
			method: "POST",
			headers: new Headers({
				"Accept": "application/json",
			}),
			body: JSON.stringify(data),
		};

		if (typeof Craft !== "undefined")
			opts.credentials = "include";

		const response = await fetch(url + action, opts);

		return await response.json();
	}
};