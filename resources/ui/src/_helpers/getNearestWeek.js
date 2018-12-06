/**
 *
 * @param {number} year
 * @param {number} month - Must be index1, not index0
 * @param {number} day
 * @return {{year: number, month: number, day: number}}
 */
export default function getNearestWeek (year, month, day) {
	const d = new Date(year, month - 1, day);

	while (d.getUTCDay() !== 1) {
		d.setUTCDate(d.getUTCDate() - 1);
	}

	return {
		year: d.getUTCFullYear(),
		month: d.getUTCMonth() + 1,
		day: d.getUTCDate(),
	};
}