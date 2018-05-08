<!--suppress JSXNamespaceValidation -->
<script>
	// import { RecycleList } from "vue-virtual-scroller";
	import Frequency from "../../const/Frequency";
	import padZero from "../../helpers/padZero";
	import getNearestWeek from "../../helpers/getNearestWeek";
	import RecursionRule from "../../models/RecursionRule";

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
		components: { /*RecycleList*/ },

		props: {
			slots: Object,
			duration: Number,
			baseRule: RecursionRule,
		},

		data () {
			const days = [
				"Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
				"Saturday", "Sunday"
			];

			return {
				days,
			};
		},

		// Computed
		// =====================================================================

		computed: {

			weeks () {
				// TODO: Return more than one week

				if (!Array.isArray(this.slots) || this.slots.length === 0) {
					const now = new Date();
					return [
						{
							beginning: getNearestWeek(
								now.getFullYear(),
								now.getMonth() + 1,
								now.getDate()
							),
						}
					];
				}

				const slot = this.slots[0];

				return [
					{
						beginning: getNearestWeek(
							slot.year,
							slot.month,
							slot.day
						),
					}
				];
			},

			formattedSlots () {
				const slots = {...this.slots};

				for (let y in slots) {
					if (!slots.hasOwnProperty(y))
						continue;

					for (let m in slots[y]) {
						if (!slots[y].hasOwnProperty(m))
							continue;

						for (let key in slots[y][m].all) {
							if (!slots[y][m].all.hasOwnProperty(key))
								continue;

							const slot = slots[y][m].all[key];

							const fullDay = 60 * 24;
							const fullHeight = (this.baseRule.frequency === Frequency.Minutely ? 1 : 60) * this.duration;
							const top = (60 * slot.hour) + slot.minute;
							const heightInclStartOffset = fullHeight + top;

							// If it won't overflow, skip
							if (heightInclStartOffset <= fullDay)
								continue;

							const extraWholeChunks = Math.floor(heightInclStartOffset / fullDay) - 1
								, extraPartialChunkHeight = heightInclStartOffset % fullDay;

							// TODO: Add different position datetimes for slots that have be split
							// TODO: For each extra whole chunk, add a new slot to the correct y/m/d
							// TODO: Add an extra slot the day after the final whole chunk with the partial chunk height (convert height to minutes)
							// TODO: Add `splitTop` & `splitBottom` to chunks accordingly

							console.log(extraWholeChunks, extraPartialChunkHeight);
						}
					}
				}

				return slots;
			},

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
							let t = i + 1;

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
							const [y, m, d] = this.correctDate(day, week, i);

							if (
								!this.formattedSlots.hasOwnProperty(y)
								|| !this.formattedSlots[y].hasOwnProperty(m)
								|| !this.formattedSlots[y][m].hasOwnProperty(d)
							) return null;

							return this.formattedSlots[y][m][d].map(id => {
								const slot = this.formattedSlots[y][m].all[id];

								return (
									<span
										key={id}
										style={this.getPosition(slot)}
										class={[this.$style.slot, {
											[this.$style['split-top']]: slot.splitTop,
											[this.$style['split-bottom']]: slot.splitBottom,
										}]}
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
				// eslint-disable-next-line no-unused-vars
				const [y, m, d] = this.correctDate(day, week, i);
				return day + ` ${d}/${m}`;
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
					d -= l;
					m = m === 12 ? 1 : m + 1;
				}

				return [y, m, d];
			},

			getPosition (slot) {
				let d = slot.day === 0 ? 7 : slot.day;

				// If Minutely or Hourly
				// (this view shouldn't be visible for other frequencies)
				let h = this.baseRule.frequency === Frequency.Minutely ? 1 : 60;
				h *= this.duration;

				return {
					left: (14.285714 * (d - 1)) + "%",
					top: (60 * slot.hour) + slot.minute + "px",
					height: h + 1 + "px",
				};
			},

			getDuration (slot) {
				const h = slot.hour === 24 ? 0 : slot.hour;

				const from = padZero(h) + ":" + padZero(slot.minute);
				let to = "";

				if (this.baseRule.frequency === Frequency.Minutely) {
					let min = slot.minute + this.duration,
						hr  = slot.hour;

					if (min >= 60) {
						min -= 60;
						hr  += 1;
					}

					if (hr >= 24)
						hr -= 24;

					to = padZero(hr) + ":" + padZero(min);
				}

				// Else Hourly
				// (this view shouldn't be visible for other frequencies)
				else {
					let hr  = slot.hour + this.duration;

					if (hr >= 24)
						hr -= 24;

					to = padZero(hr) + ":" + padZero(slot.minute);
				}

				return from + " - " + to;
			}

		},
	};
</script>

<style module lang="less">
	@import "../../variables";

	@rowHeight: 60px;

	.scroller {
		overflow: auto;
	}

	.group {
		position: relative;
		width: 100%;
		height: @rowHeight * 25;
	}

	.header,
	.row {
		display: flex;

		height: @rowHeight;
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
				height: @rowHeight;

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
		height: calc(~"100% - "@rowHeight);
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

		// TODO: Uncomment me!
		/*overflow: hidden;*/

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
					transparent (@rowHeight - 1),
					@border (@rowHeight - 1),
					@border @rowHeight
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
			height: @rowHeight;
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

		&.split-top {
			border-top: none;

			&:before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;

				display: block;
				width: 100%;
				height: 4px;

				background: repeating-linear-gradient(
					135deg,
					transparent, transparent .4em /* black stripe */,
					#3FE79E 0, #3FE79E .75em /* blue stripe */
				);
			}
		}

		&.split-bottom {
			border-bottom: none;

			&:after {
				content: '';
				position: absolute;
				bottom: 0;
				left: 0;

				display: block;
				width: 100%;
				height: 4px;

				background: repeating-linear-gradient(
					135deg,
					transparent, transparent .4em /* black stripe */,
					#3FE79E 0, #3FE79E .75em /* blue stripe */
				);
			}
		}
	}
</style>