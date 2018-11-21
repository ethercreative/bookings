/* global Craft */

export async function get (action, params = {}, asText = false) {
	params = Object.keys(params)
		.map(k => `${encodeURIComponent(k)}=${encodeURIComponent(params[k])}`)
		.join('&');

	let url = Craft.getActionUrl(action);
	if (params !== '') url += '&' + params;

	return fetch(url, {
		method: 'GET',
		headers: {
			'Accepts': 'application/json',
			'X-CSRF-Token': Craft.csrfTokenValue,
		},
	}).then(res => asText ? res.text() : res.json());
}

export async function post (action, body = {}) {
	return fetch(Craft.getActionUrl(action), {
		method: 'POST',
		headers: {
			'Accepts': 'application/json',
			'X-CSRF-Token': Craft.csrfTokenValue,
		},
		body: JSON.stringify(body),
	}).then(res => res.json());
}