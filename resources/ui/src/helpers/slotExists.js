export default function slotExists (slots, y, m, d) {
	return (
		slots.hasOwnProperty(y)
		&& slots[y].hasOwnProperty(m)
		&& slots[y][m].hasOwnProperty(d)
	);
}