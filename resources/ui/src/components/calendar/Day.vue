<!--suppress JSXNamespaceValidation -->
<script>
	import RecursionRule from "../../models/RecursionRule";
	import correctDate from "../../helpers/correctDate";
	import slotExists from "../../helpers/slotExists";
	import getFirstSlot from "../../helpers/getFirstSlot";
	import padZero from "../../helpers/padZero";

	const MONTHS = [
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
		"December",
	];

	const TIMES = [
		"10 mins",
		"20 mins",
		"30 mins",
		"40 mins",
		"50 mins",
		"60 mins",
	];

	export default {
		name: "Day",

		props: {
			slots: Object,
			exceptions: Object,
			duration: Number,
			baseRule: RecursionRule,
		},

		// Computed
		// =====================================================================

		computed: {

			days () {
				const startDate = getFirstSlot(
					this.slots,
					{ date: new Date() }
				).date;

				const firstDay = {
					year: startDate.getFullYear(),
					month: startDate.getMonth() + 1,
					day: startDate.getDate(),
				};

				const days = [firstDay];

				// TODO: Make i dynamic
				let i = 3,
					prevDay = firstDay;

				while (--i) {
					const [year, month, day] = correctDate(
						prevDay.year,
						prevDay.month,
						prevDay.day + 1
					);

					const d = { year, month, day };

					days.push(d);
					prevDay = d;
				}

				return days;
			},

			formattedSlots () {
				return {
					slots: this.formatSlots({ ...this.slots }),
					exceptions: this.formatSlots({ ...this.exceptions }),
				};
			},

		},

		// Render
		// =====================================================================

		render () {
			return (
				<div class={this.$style.scroller}>
					{this.days.map((day, index) => (
						<div key={index} class={this.$style.group}>
							{this._renderHeader(day)}
							{this._renderLabels()}
							{this._renderCells(day)}
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

			_renderHeader (day) {
				return (
					<header class={this.$style.header}>
						<div>
							{TIMES.map((time, i) => (
								<span key={i}>
									{i === 0 && (
										<span>{this.getHeader(day)}</span>
									)}
									{time}
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

			_renderCells (day) {
				return (
					<div class={this.$style.cells}>
						{TIMES.map((time, i) => {
							const [y, m, d] = this.correctDateByDay(day, i);

							if (!slotExists(this.formattedSlots.slots, y, m, d))
								return null;

							return this.formattedSlots.slots[y][m][d].map(id => {
								const slot = this.formattedSlots.slots[y][m].all[id];

								return (
									<span
										key={id}
										style={slot.position}
										class={[this.$style.slot, {
											[this.$style['split-left']]: slot.splitLeft,
											[this.$style['split-right']]: slot.splitRight,
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

						{TIMES.map((time, i) => {
							const [y, m, d] = this.correctDateByDay(day, i);

							if (!slotExists(this.formattedSlots.exceptions, y, m, d))
								return null;

							return this.formattedSlots.exceptions[y][m][d].map(id => {
								const slot = this.formattedSlots.exceptions[y][m].all[id];

								return (
									<span
										key={id}
										style={slot.position}
										class={this.$style.exception}
									/>
								);
							});
						})}
					</div>
				);
			},

			// Helpers
			// -----------------------------------------------------------------

			getHeader (day) {
				// eslint-disable-next-line no-unused-vars
				const [y, m, d] = this.correctDateByDay(day, 0);
				return d + " " + MONTHS[m - 1];
			},

			correctDateByDay (day, i) {
				return correctDate(
					day.year,
					day.month,
					day.day + i
				);
			},

			getPosition (hour, minutes, duration = this.duration) {
				return {
					top: (hour * 60) + "px",
					left: ((minutes / 60) * 100) + "%",
					// This assumes frequency === minutely, view should be
					// disabled for other frequencies
					width: ((duration / 60) * 100) + "%",
				};
			},

			getDuration (slot) {
				const h = slot.hour === 24 ? 0 : slot.hour;

				const from = padZero(h) + ":" + padZero(slot.minute);
				let to = "";

				let min = slot.minute + this.duration,
					hr  = slot.hour;

				if (min >= 60) {
					min -= 60;
					hr  += 1;
				}

				if (hr >= 24)
					hr -= 24;

				to = padZero(hr) + ":" + padZero(min);

				return from + " - " + to;
			},

			formatSlots (slots) {
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

							const fullWidth = this.duration;
							const widthInclStartOffset = fullWidth + slot.minute;

							// If it won't overflow, set position & skip
							if (widthInclStartOffset <= 60) {
								slots[y][m].all[key] = {
									...slot,
									position: this.getPosition(slot.hour, slot.minute),
								};
								continue;
							}

							// 1. Set the position of the original chunk
							slots[y][m].all[key] = {
								...slot,
								position: this.getPosition(
									slot.hour,
									slot.minute,
									this.duration + (60 - widthInclStartOffset)
								),
								splitRight: true,
							};

							// 2. Add additional chunks

							// Calculate how many extra hours & minutes (chunks)
							// this slot overflows by.
							const extraPartialChunkWidth = widthInclStartOffset % 60;
							let extraWholeChunks = Math.floor(widthInclStartOffset / 60),
								previousDate = slot.date,
								previousHour = slot.date.getHours();

							if (widthInclStartOffset === 120)
								extraWholeChunks--;

							while (extraWholeChunks--) {
								const nDate = new Date(
									previousDate.getFullYear(),
									previousDate.getMonth(),
									previousDate.getDate(),
									previousHour + 1,
									0, 0, 0
								);
								const nKey = nDate.getTime();

								const ny = nDate.getFullYear()
									, nm = nDate.getMonth() + 1
									, nd = nDate.getDate();

								if (!slots.hasOwnProperty(ny))
									slots[ny] = {};

								if (!slots[ny].hasOwnProperty(nm))
									slots[ny][nm] = { all: {} };

								if (!slots[ny][nm].hasOwnProperty(nd))
									slots[ny][nm][nd] = [];

								const isPartial = extraWholeChunks === 0 && extraPartialChunkWidth > 0;

								slots[ny][nm].all[nKey] = {
									...slot,
									position: this.getPosition(
										nDate.getHours(),
										0,
										isPartial ? extraPartialChunkWidth : 60
									),
									splitLeft: true,
									splitRight: extraWholeChunks !== 0,
								};

								if (slots[ny][nm][nd].indexOf(nKey) === -1)
									slots[ny][nm][nd].push(nKey);

								previousDate = nDate;
								previousHour = nDate.getHours();
							}
						}
					}
				}

				return slots;
			},

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

			border-left: 1px solid @border;

			> span {
				display: inline-flex;
				align-items: center;
				justify-content: flex-end;
				width: 100% / 6;
				height: @rowHeight;
				padding: 0 10px;

				color: #8C97B2;
				font-size: 12px;
				text-transform: uppercase;

				> span {
					font-weight: bold;
					text-transform: none;
				}

				&:first-child {
					justify-content: space-between;
				}
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
				to bottom,
				transparent 0px,
				transparent 3px,
				#fff 3px,
				#fff 6px
			),
			repeating-linear-gradient(
				to right,
				transparent 0px,
				transparent calc(100% / 6~" - 1px"),
				#fff calc(100% / 6~" - 1px"),
				#fff 100% / 6
			),
			repeating-linear-gradient(
				to right,
				transparent 0px,
				transparent calc(100% / 60~" - 1px"),
				fade(@border, 50%) calc(100% / 60~" - 1px"),
				fade(@border, 50%) 100% / 60
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
					transparent 100% / 6
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

	.exception {
		position: absolute;

		display: block;
		height: @rowHeight;

		background: linear-gradient(
			to right,
			fade(@craft-primary, 10%),
			fade(@craft-primary, 0%)
		);
	}

	.slot {
		position: absolute;

		display: flex;
		align-items: center;
		justify-content: center;
		height: @rowHeight + 1;
		margin: -1px 0 0;

		color: #3FE79E;
		font-size: 9px;
		font-weight: bold;
		letter-spacing: 0.9px;
		line-height: normal;
		text-align: center;
		text-transform: uppercase;

		background: rgba(63,231,158,0.15);
		border: 1px solid #3FE79E;
		border-left-width: 2px;
		border-right-width: 2px;

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

		&.split-left {
			border-left: none;

			&:before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;

				display: block;
				width: 4px;
				height: 100%;

				background: repeating-linear-gradient(
					135deg,
					transparent, transparent .4em /* black stripe */,
					#3FE79E 0, #3FE79E .75em /* blue stripe */
				);
			}
		}

		&.split-right {
			border-right: none;

			&:after {
				content: '';
				position: absolute;
				top: 0;
				right: 0;

				display: block;
				width: 4px;
				height: 100%;

				background: repeating-linear-gradient(
					135deg,
					transparent, transparent .4em /* black stripe */,
					#3FE79E 0, #3FE79E .75em /* blue stripe */
				);
			}
		}
	}
</style>