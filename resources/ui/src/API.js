export default {
	async postActionRequest (action, data) {
		return fetch(`https://dev.craft3/index.php?p=admin/actions/${action}`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				"X-Requested-With": "XMLHttpRequest",
			},
			credentials: "include",
			body: JSON.stringify(data),
		}).then(r => r.json());
	}
};