<!--suppress JSXNamespaceValidation -->
<script>
	// import { RecycleList } from "vue-virtual-scroller";
	import Frequency from "../../enums/Frequency";
	import padZero from "../../helpers/padZero";
	import getNearestWeek from "../../helpers/getNearestWeek";
	import RecursionRule from "../../models/RecursionRule";
	import correctDate from "../../helpers/correctDate";
	import slotExists from "../../helpers/slotExists";
	import getFirstSlot from "../../helpers/getFirstSlot";

	const DAYS = [
		"Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
		"Saturday", "Sunday"
	];

	const FULL_DAY = 60 * 24;

	export default {
		name: "Week",
		components: { /*RecycleList*/ },

		props: {
			slots: Object,
			duration: Number,
			baseRule: RecursionRule,
		},

		// Computed
		// =====================================================================

		computed: {

			weeks () {
				const startDate = getFirstSlot(
					this.slots,
					{ date: new Date() }
				).date;

				const firstWeek = getNearestWeek(
					startDate.getFullYear(),
					startDate.getMonth() + 1,
					startDate.getDate()
				);

				const weeks = [firstWeek];

				// TODO: i should either:
				// Until: Encompass the until date
				// # Times: The end date of the final slot
				// Forever: Set to 100
				// Should be capped at 100.
				let i = 3,
					prevWeek = firstWeek;

				while (--i) {
					const [year, month, day] = correctDate(
						prevWeek.year,
						prevWeek.month,
						prevWeek.day + 7
					);

					const week = { year, month, day };

					weeks.push(week);
					prevWeek = week;
				}

				return weeks;
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

							// Convert the frequency into minutes
							const numericFreq = this.baseRule.frequency === Frequency.Minutely ? 1 : 60;

							const fullHeight = numericFreq * this.duration;
							const top = (60 * slot.hour) + slot.minute;
							const heightInclStartOffset = fullHeight + top;

							// If it won't overflow, set position & skip
							if (heightInclStartOffset <= FULL_DAY) {
								slots[y][m].all[key] = {
									...slot,
									position: this.getPosition(slot.day, slot.hour, slot.minute),
								};
								continue;
							}

							// 1. Set the position of the original chunk
							slots[y][m].all[key] = {
								...slot,
								position: this.getPosition(
									slot.day,
									slot.hour,
									slot.minute,
									this.duration + ((FULL_DAY - heightInclStartOffset) / numericFreq)
								),
								splitBottom: true,
							};

							// 2. Add additional chunks

							// Calculate how many extra days & minutes (chunks)
							// this slot overflows by.
							const extraPartialChunkHeight = heightInclStartOffset % FULL_DAY;
							let extraWholeChunks = Math.floor(heightInclStartOffset / FULL_DAY),
								previousDate = slot.date.getDate();

							while (extraWholeChunks--) {
								const [ny, nm, nd] = correctDate(y, m, previousDate + 1);
								const nDate = new Date(ny, nm - 1, nd, 0, 0, 0, 0);
								const nKey = nDate.getTime();

								if (!slots.hasOwnProperty(ny))
									slots[ny] = {};

								if (!slots[ny].hasOwnProperty(nm))
									slots[ny][nm] = { all: {} };

								if (!slots[ny][nm].hasOwnProperty(nd))
									slots[ny][nm][nd] = [];

								const isPartial = extraWholeChunks === 0 && extraPartialChunkHeight > 0;

								slots[ny][nm].all[nKey] = {
									...slot,
									position: this.getPosition(
										nDate.getDay(),
										0,
										0,
										isPartial ? extraPartialChunkHeight / numericFreq : 24
									),
									splitTop: true,
									splitBottom: !isPartial,
								};

								if (slots[ny][nm][nd].indexOf(nKey) === -1)
									slots[ny][nm][nd].push(nKey);

								previousDate = nd;
							}
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
							{DAYS.map((day, i) => (
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
						{DAYS.map((day, i) => {
							const [y, m, d] = this.correctDateByWeek(week, i);

							if (!slotExists(this.formattedSlots, y, m, d))
								return null;

							return this.formattedSlots[y][m][d].map(id => {
								const slot = this.formattedSlots[y][m].all[id];

								return (
									<span
										key={id}
										style={slot.position}
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
				const [y, m, d] = this.correctDateByWeek(week, i);
				return day + ` ${d}/${m}`;
			},

			correctDateByWeek (week, i) {
				return correctDate(
					week.year,
					week.month,
					week.day + i
				);
			},

			getPosition (day, hour, minute, duration = this.duration) {
				let d = day === 0 ? 7 : day;

				// If Minutely or Hourly
				// (this view shouldn't be visible for other frequencies)
				let h = this.baseRule.frequency === Frequency.Minutely ? 1 : 60;
				h *= duration;

				return {
					left: (14.285714 * (d - 1)) + "%",
					top: (60 * hour) + minute + "px",
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
				transparent 100% / 7
			);

			span {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 100% / 7;
				height: @rowHeight;

				color: #8C97B2;
				font-size: 12px;
				text-align: center;
				text-transform: uppercase;
			}
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
		overflow: hidden;

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
					transparent 100% / 7
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

	.slot {
		position: absolute;

		display: flex;
		align-items: center;
		justify-content: center;
		width: calc(100% / 7 ~" + 1px");
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
		border-top-width: 2px;
		border-bottom-width: 2px;

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