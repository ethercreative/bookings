/**
 *
 * @param {number} year
 * @param {number} month - Must be index1, not index0
 * @param {number} day
 * @return {{year: number, month: number, day: number}}
 */
export default function getNearestWeek (year, month, day) {
	const d = new Date(year, month - 1, day);

	while (d.getDay() !== 1) {
		d.setDate(d.getDate() - 1);
	}

	return {
		year: d.getFullYear(),
		month: d.getMonth() + 1,
		day: d.getDate(),
	};
}