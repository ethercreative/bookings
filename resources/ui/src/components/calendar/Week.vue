<!--suppress JSXNamespaceValidation -->
<script>
	import { RecycleList } from "vue-virtual-scroller";

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

	export default {
		name: "Week",
		components: { RecycleList },

		props: {
			slots: Object,
		},

		data () {
			const days = [
				"Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
				"Saturday", "Sunday"
			];

			return {
				days,
				weeks: [
					{ beginning: { year: 2018, month: 3, day: 26 } },
					{ beginning: { year: 2018, month: 4, day: 2 } },
					{ beginning: { year: 2018, month: 4, day: 9 } },
					{ beginning: { year: 2018, month: 4, day: 16 } },
					{ beginning: { year: 2018, month: 4, day: 23 } },
				],
			};
		},

		// Render
		// =====================================================================

		render () {
			return (
				<div class={this.$style.scroller}>
					{this.weeks.map((week, index) => (
						<div key={index} class={this.$style.group}>
							{this._renderHeader(week)}
							{this._renderLabels()}
							{this._renderCells(week)}
						</div>
					))}
				</div>
			);
		},

		// Methods
		// =====================================================================

		methods: {

			// Render
			// -----------------------------------------------------------------

			_renderHeader (week) {
				return (
					<header class={this.$style.header}>
						<div>
							{this.days.map((day, i) => (
								<span key={i}>
									{this.getHeader(day, week, i)}
								</span>
							))}
						</div>
					</header>
				);
			},

			_renderLabels () {
				return (
					<ul class={this.$style.labels}>
						{Array.from({length: 23}, (_, i) => {
							let t = i + 2;

							if (t > 12)
								t = (t - 12) + " pm";
							else
								t = t + " am";

							return (
								<li key={i}>{t}</li>
							);
						})}
					</ul>
				);
			},

			_renderCells (week) {
				return (
					<div class={this.$style.cells}>
						{this.days.map((day, i) => {
							const [m, d] = this.correctDate(day, week, i);

							if (!this.slots.hasOwnProperty(m) || !this.slots[m].hasOwnProperty(d))
								return null;

							return this.slots[m][d].map(id => {
								const slot = this.slots[m].all[id];

								return (
									<span
										key={id}
										style={this.getPosition(slot)}
										class={this.$style.slot}
									>
										<span>
											Bookable
											<em>{this.getDuration(slot)}</em>
										</span>
									</span>
								);
							});
						})}
					</div>
				);
			},

			// Helpers
			// -----------------------------------------------------------------

			getHeader (day, week, i) {
				const [m, d] = this.correctDate(day, week, i);
				return day + ` ${m}/${d}`;
			},

			correctDate (day, week, i) {
				const y = week.beginning.year;

				let m = week.beginning.month,
					d = week.beginning.day + i;

				let l = MONTH_LENGTHS[m - 1];

				// If leap year & is Feb increase month len by 1
				if ((((y % 4 === 0) && (y % 100 !== 0)) || (y % 400 === 0)) && m === 1)
					l++;

				// If the date is greater than the month len, wrap to next month
				if (d > l) {
					d = 1;
					m = m === 12 ? 1 : m + 1;
				}

				return [m, d];
			},

			getPosition (slot) {
				const d = slot.day === 0 ? 7 : slot.day;

				return {
					left: (14.285714 * (d - 1)) + "%",
					top: (60 * (slot.hour - 1)) + slot.minute + "px",
				};
			},

			getDuration (slot) {
				const h = slot.hour === 24 ? 0 : slot.hour
					, m = ":" + this.padZero(slot.minute);

				// TODO: Since we don't have duration atm, this is simply adding 1 to the hour
				const from = this.padZero(h) + m
					, to   = this.padZero(h + 1) + m;

				return from + " - " + to;
			},

			padZero (value) {
				if (value < 10) return '0' + value;
				return value;
			},

		},
	};
</script>

<style module lang="less">
	@import "../../variables";

	.scroller {
		overflow: auto;
	}

	.group {
		position: relative;
		width: 100%;
		height: 60px * 25;
	}

	.header,
	.row {
		display: flex;

		height: 60px;
		padding-left: 100px;
	}

	.header {
		position: sticky;
		z-index: 2;
		top: 0;

		background-color: #fff;
		border-bottom: 1px solid @border;

		div {
			width: 100%;

			background: repeating-linear-gradient(
				to right,
				@border 0%,
				@border calc(~"0% + 1px"),
				transparent calc(~"0% + 1px"),
				transparent 14.285714%
			);

			span {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 14.285714%;
				height: 60px;

				color: #8C97B2;
				font-size: 12px;
				text-align: center;
				text-transform: uppercase;
			}
		}
	}

	.cells {
		position: relative;

		width: calc(~"100% - 100px");
		height: 100%;
		margin-left: 100px;

		background:
			repeating-linear-gradient(
				to right,
				transparent 0px,
				transparent 3px,
				#fff 3px,
				#fff 6px
			),
			repeating-linear-gradient(
				to bottom,
				transparent 0px,
				transparent 29px,
				@border 29px,
				@border 30px
			);

		&:before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;

			background:
				repeating-linear-gradient(
					to right,
					@border 0%,
					@border calc(~"0% + 1px"),
					transparent calc(~"0% + 1px"),
					transparent 14.285714%
				),
				repeating-linear-gradient(
					to bottom,
					transparent 0px,
					transparent 59px,
					@border 59px,
					@border 60px
				);
		}
	}

	.labels {
		position: absolute;
		top: 0;
		left: 0;

		margin: 0;
		padding: 110px 0 0;
		list-style: none;

		li {
			display: block;
			width: 100px;
			height: 60px;
			padding-right: 10px;

			color: #8C97B2;
			font-size: 14px;
			text-align: right;
			text-transform: uppercase;
		}
	}

	.slot {
		position: absolute;

		display: flex;
		align-items: center;
		justify-content: center;
		width: calc(~"14.285714% + 1px");
		height: 61px; // TODO: Make dynamic based off frequency
		margin: -1px 0 0 0;

		color: #3FE79E;
		font-size: 9px;
		font-weight: bold;
		letter-spacing: 0.9px;
		line-height: normal;
		text-align: center;
		text-transform: uppercase;

		background: rgba(63,231,158,0.15);
		border: 1px solid #3FE79E;

		span {
			margin-top: 4px;
		}

		em {
			display: block;
			margin-top: 5px;

			color: #29A871;
			font-size: 14px;
			font-weight: bold;
			font-style: normal;
		}
	}
</style>