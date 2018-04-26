/* global Craft */

export default {
	async postActionRequest (action, data) {
		const url = typeof Craft !== "undefined"
			? Craft.actionUrl
			: 'https://dev.craft3/index.php?p=actions/';

		const response = await fetch(url + action, {
			method: "POST",
			headers: new Headers({
				"Accept": "application/json",
			}),
			body: JSON.stringify(data),
		});

		return await response.json();
	}
};