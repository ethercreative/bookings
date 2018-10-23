const shortMonths = [
	"Jan",
	"Feb",
	"Mar",
	"Apr",
	"May",
	"Jun",
	"Jul",
	"Aug",
	"Sep",
	"Oct",
	"Nov",
	"Dec"
]
	, longMonths = [
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December"
]
	, shortDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
	, longDays = [
	"Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday"
];

// Defining patterns
const replaceChars = {
	// Day
	// -------------------------------------------------------------------------

	/**
	 * @returns {string}
	 */
	d: function () {
		const d = this.getDate();
		return (d < 10 ? '0' : '') + d;
	},

	/**
	 * @returns {string}
	 */
	D: function () {
		return shortDays[this.getDay()];
	},

	/**
	 * @returns {number}
	 */
	j: function () {
		return this.getDate();
	},

	/**
	 * @returns {string}
	 */
	l: function () {
		return longDays[this.getDay()];
	},

	/**
	 * @returns {number}
	 */
	N: function () {
		const N = this.getDay();
		return N === 0 ? 7 : N;
	},

	/**
	 * @returns {string}
	 */
	S: function () {
		const S = this.getDate();
		return (
			S % 10 === 1 && S !== 11 ? 'st' : (
				S % 10 === 2 && S !== 12 ? 'nd' : (
					S % 10 === 3 && S !== 13 ? 'rd' : 'th'
				)
			)
		);
	},

	/**
	 * @returns {number}
	 */
	w: function () {
		return this.getDay();
	},

	/**
	 * @returns {number}
	 */
	z: function () {
		const d = new Date(this.getFullYear(), 0, 1);
		return Math.ceil((this - d) / 86400000);
	},

	// Week
	// -------------------------------------------------------------------------

	/**
	 * @returns {string}
	 */
	W: function () {
		const target = new Date(this.valueOf())
			, dayNr = (this.getDay() + 6) % 7;

		target.setDate(target.getDate() - dayNr + 3);

		const firstThursday = target.valueOf();

		target.setMonth(0, 1);

		if (target.getDay() !== 4)
			target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);

		const retVal = 1 + Math.ceil((firstThursday - target) / 604800000);

		return (retVal < 10 ? '0' + retVal : retVal);
	},

	// Month
	// -------------------------------------------------------------------------

	/**
	 * @returns {string}
	 */
	F: function () {
		return longMonths[this.getMonth()];
	},

	/**
	 * @returns {string}
	 */
	m: function () {
		const m = this.getMonth();
		return (m < 9 ? '0' : '') + (m + 1);
	},

	/**
	 * @returns {string}
	 */
	M: function () {
		return shortMonths[this.getMonth()];
	},

	/**
	 * @returns {number}
	 */
	n: function () {
		return this.getMonth() + 1;
	},

	/**
	 * @returns {number}
	 */
	t: function () {
		let year = this.getFullYear(), nextMonth = this.getMonth() + 1;

		if (nextMonth === 12) {
			year++;
			nextMonth = 0;
		}

		return new Date(year, nextMonth, 0).getDate();
	},

	// Year
	// -------------------------------------------------------------------------

	/**
	 * @returns {boolean}
	 */
	L: function () {
		const L = this.getFullYear();
		return (
			L % 400 === 0 || (
				L % 100 !== 0 && L % 4 === 0
			)
		);
	},

	/**
	 * @returns {number}
	 */
	o: function () {
		const d = new Date(this.valueOf());
		d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3);
		return d.getFullYear();
	},

	/**
	 * @returns {number}
	 */
	Y: function () {
		return this.getFullYear();
	},

	/**
	 * @returns {string}
	 */
	y: function () {
		return (
			'' + this.getFullYear()
		).substr(2);
	},

	// Time
	// -------------------------------------------------------------------------

	/**
	 * @returns {string}
	 */
	a: function () {
		return this.getHours() < 12 ? 'am' : 'pm';
	},

	/**
	 * @returns {string}
	 */
	A: function () {
		return this.getHours() < 12 ? 'AM' : 'PM';
	},

	/**
	 * @returns {number}
	 */
	B: function () {
		return Math.floor(
			(
				(
					(
						this.getUTCHours() + 1
					) % 24
				) + this.getUTCMinutes() / 60
				+ this.getUTCSeconds() / 3600
			) * 1000 / 24);
	},

	/**
	 * @returns {number}
	 */
	g: function () {
		return this.getHours() % 12 || 12;
	},

	/**
	 * @returns {number}
	 */
	G: function () {
		return this.getHours();
	},

	/**
	 * @returns {string}
	 */
	h: function () {
		const h = this.getHours();
		return (
			(
				h % 12 || 12
			) < 10 ? '0' : ''
		) + (
			h % 12 || 12
		);
	},

	/**
	 * @returns {string}
	 */
	H: function () {
		const H = this.getHours();
		return (H < 10 ? '0' : '') + H;
	},

	/**
	 * @returns {string}
	 */
	i: function () {
		const i = this.getMinutes();
		return (
			i < 10 ? '0' : ''
		) + i;
	},

	/**
	 * @returns {string}
	 */
	s: function () {
		const s = this.getSeconds();
		return (
			s < 10 ? '0' : ''
		) + s;
	},

	/**
	 * @returns {string}
	 */
	v: function () {
		const v = this.getMilliseconds();
		return (
			v < 10 ? '00' : (
				v < 100 ? '0' : ''
			)
		) + v;
	},

	// Timezone
	// -------------------------------------------------------------------------

	/**
	 * @returns {string}
	 */
	e: function () {
		return Intl.DateTimeFormat().resolvedOptions().timeZone;
	},

	/**
	 * @returns {number}
	 */
	I: function () {
		let DST = null;
		for (let i = 0; i < 12; ++i) {
			const d = new Date(this.getFullYear(), i, 1);
			const offset = d.getTimezoneOffset();

			if (DST === null) DST = offset;
			else if (offset < DST) {
				DST = offset;
				break;
			} else if (offset > DST) break;
		}
		return (this.getTimezoneOffset() === DST) | 0;
	},

	/**
	 * @returns {string}
	 */
	O: function () {
		const O = this.getTimezoneOffset();
		return (
			-O < 0 ? '-' : '+'
		) + (
			Math.abs(O / 60) < 10 ? '0' : ''
		) + Math.floor(Math.abs(O / 60)) + (
			Math.abs(O % 60) === 0 ? '00' : (
				(
					Math.abs(O % 60) < 10 ? '0' : ''
				)
			) + (
				Math.abs(O % 60)
			)
		);
	},

	/**
	 * @returns {string}
	 */
	P: function () {
		const P = this.getTimezoneOffset();
		return (
			-P < 0 ? '-' : '+'
		) + (
			Math.abs(P / 60) < 10 ? '0' : ''
		) + Math.floor(Math.abs(P / 60)) + ':' + (
			Math.abs(P % 60) === 0 ? '00' : (
				(
					Math.abs(P % 60) < 10 ? '0' : ''
				)
			) + (
				Math.abs(P % 60)
			)
		);
	},

	/**
	 * @returns {string}
	 */
	T: function () {
		const tz = this.toLocaleTimeString(
			navigator.language,
			{ timeZoneName: 'short' }
		).split(' ');
		return tz[tz.length - 1];
	},

	/**
	 * @returns {number}
	 */
	Z: function () {
		return -this.getTimezoneOffset() * 60;
	},

	// Full Date / Time
	// -------------------------------------------------------------------------

	/**
	 * @returns {*|string}
	 */
	c: function () {
		return this.format("Y-m-d\\TH:i:sP");
	},

	/**
	 * @returns {string}
	 */
	r: function () {
		return this.toString();
	},

	/**
	 * @returns {number}
	 */
	U: function () {
		return Math.floor(this.getTime() / 1000);
	},
};

/**
 * Simulates PHP's date function
 *
 * @param {Date} date
 * @param {string}  format
 */
export default function format (date, format) {
	return format.replace(/(\\?)(.)/g, function (_, esc, chr) {
		return (esc === '' && replaceChars[chr]) ? replaceChars[chr].call(date) : chr;
	});
}