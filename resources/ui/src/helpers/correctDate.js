const MONTH_LENGTHS = [
	31, // Jan
	28, // Feb
	31, // Mar
	30, // Apr
	31, // May
	30, // Jun
	31, // Jul
	31, // Aug
	30, // Sep
	31, // Oct
	30, // Nov
	31, // Dec
];

/**
 * @param {number} y - Year
 * @param {number} m - Month
 * @param {number} d - Date
 * @return {*[]}
 */
export default function correctDate (y, m, d) {
	let l = MONTH_LENGTHS[m - 1];

	// If leap year & is Feb increase month len by 1
	if ((((y % 4 === 0) && (y % 100 !== 0)) || (y % 400 === 0)) && m === 1)
		l++;

	// If the date is greater than the month len, wrap to next month
	if (d > l) {
		d -= l;
		const wrapYear = m === 12;
		m = wrapYear ? 1 : m + 1;
		wrapYear && y++;
	}

	return [y, m, d];
}