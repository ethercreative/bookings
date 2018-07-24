export default function getLastSlot (slots, fallback = null) {
	const y = Object.keys(slots).pop();
	console.log(y);

	if (typeof y === "undefined")
		return fallback;

	const m = Object.keys(slots[y]).pop();
	console.log(m);

	if (typeof m === "undefined")
		return fallback;

	const key = Object.keys(slots[y][m].all).pop();
	console.log(key);

	if (typeof key === "undefined")
		return fallback;

	return slots[y][m].all[key];
}