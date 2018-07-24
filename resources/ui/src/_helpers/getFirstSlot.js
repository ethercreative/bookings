export default function getFirstSlot (slots, fallback = null) {
	const y = Object.keys(slots)[0];

	if (typeof y === "undefined")
		return fallback;

	const m = Object.keys(slots[y])[0];

	if (typeof m === "undefined")
		return fallback;

	const key = Object.keys(slots[y][m].all)[0];

	if (typeof key === "undefined")
		return fallback;

	return slots[y][m].all[key];
}