<template>
	<div :class="$style.scroller">
		<div
			v-for="(week, index) in weeks"
			:key="index"
			:class="$style.group"
		>
			<header :class="$style.header">
				<div>
					<span v-for="(day, i) in days" :key="day">
						{{ day + ` ${week.beginning.month}/${week.beginning.day + i}` }}
					</span>
				</div>
			</header>

			<ul :class="$style.labels">
				<li v-for="t in 23" :key="t">
					{{ t+1 > 12 ? ((t+1) - 12) + " pm" : t+1 + " am" }}
				</li>
			</ul>

			<div :class="$style.cells">
				<div
					v-for="(day, i) in days"
					:key="day"
					v-if="slots.hasOwnProperty(week.beginning.month) && slots[week.beginning.month][week.beginning.day + i]"
				>
					<span
						v-for="slot in slots[week.beginning.month][week.beginning.day + i]"
						:key="slot"
						:style="getPosition(slots[week.beginning.month].all[slot])"
						:class="$style.slot"
					>
						<span>
							Bookable
							<em>{{ getDuration(slots[week.beginning.month].all[slot]) }}</em>
						</span>
					</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { RecycleList } from "vue-virtual-scroller";

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
					{ beginning: { month: 4, day: 1 } },
					{ beginning: { month: 4, day: 8 } },
					{ beginning: { month: 4, day: 15 } },
					{ beginning: { month: 4, day: 22 } },
				],
			};
		},

		methods: {

			getPosition (slot) {
				const h = slot.hour === 0 ? 24 : slot.hour;
				const d = h === 24 ? slot.day - 1 : slot.day;

				return {
					left: (14.285714 * d) + "%",
					top: (60 * (h - 1)) + slot.minute + "px",
				};
			},

			getDuration (slot) {
				// TODO: Handle slot duration

				return this.padZero(slot.hour) + ":" + this.padZero(slot.minute);
			},

			// Helpers
			// -----------------------------------------------------------------

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
		height: 61px; // TODO: Make dynamic based off duration
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