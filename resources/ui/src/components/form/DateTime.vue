<template>
	<div :class="$style.wrap">
		<label :class="$style.date">
			<input
				type="text"
				readonly
				@focus="openDateModal"
				ref="dateInput"
				:value="formatDate(value, 'd/m/Y')"
				:class="$style.input"
			/>

			<Modal
				:open="isDateOpen"
				:on-request-close="onRequestCloseDate"
				:clear="true"
				:parented="true"
				:shallow="true"
			>
				<date-picker
					:inline="true"
					:value="value"
					@input="onInput"
					:calendar-class="$style.calendar"
				/>
			</Modal>
		</label>

		<label :class="$style.time">
			<input
				type="text"
				readonly
				@focus="openTimeModal"
				ref="timeInput"
				:value="formatDate(value, 'H:i')"
				:class="$style.input"
			/>

			<Modal
				:open="isTimeOpen"
				:on-request-close="onRequestCloseTime"
				:clear="true"
				:parented="true"
				:shallow="true"
			>
				<div :class="$style.times">
					<ul>
						<li v-for="h in 12" :key="h">
							<button
								:class="{[$style.active]:isActiveHour(h)}"
								:disabled="isActiveHour(h)"
								@click="setHour(h)"
							>
								{{ padZero(h) }}
							</button>
						</li>
					</ul>
					<ul>
						<li v-for="m in 60" :key="m">
							<button
								:class="{[$style.active]:isActiveMinute(m)}"
								:disabled="isActiveMinute(m)"
								@click="setMinute(m)"
							>
								{{ padZero(m - 1) }}
							</button>
						</li>
					</ul>
					<ul>
						<li>
							<button
								:class="{[$style.active]:isActivePeriod(true)}"
								:disabled="isActivePeriod(true)"
								@click="setPeriod(true)"
							>
								AM
							</button>
						</li>
						<li>
							<button
								:class="{[$style.active]:isActivePeriod(false)}"
								:disabled="isActivePeriod(false)"
								@click="setPeriod(false)"
							>
								PM
							</button>
						</li>
					</ul>
				</div>
			</Modal>
		</label>
	</div>
</template>

<script>
	import DatePicker from "vuejs-datepicker";
	import formatDate from "../../helpers/formatDate";
	import Modal from "../Modal";

	export default {
		name: "DateTime",
		components: { Modal, DatePicker },

		data () {
			const now = new Date();

			return {
				isDateOpen: false,
				isTimeOpen: false,
				value: now,
			};
		},

		methods: {

			openDateModal () {
				this.$refs.dateInput.blur();
				this.isDateOpen = true;
			},

			onRequestCloseDate () {
				this.isDateOpen = false;
			},

			openTimeModal () {
				this.$refs.timeInput.blur();
				this.isTimeOpen = true;
			},

			onRequestCloseTime () {
				this.isTimeOpen = false;
			},

			onInput (value) {
				this.value = value;
				this.emitInput();
			},

			emitInput () {
				const value = this.value;
				this.$emit("input", value);
			},

			// ---

			formatDate,

			padZero (value) {
				if (value < 10) return '0' + value;
				return value;
			},

			// ---

			isActiveHour (value) {
				const hours = this.value.getHours();

				if (hours >= 12) return hours === value + 12;
				return hours === value;
			},

			isActiveMinute (value) {
				value -= 1;
				return this.value.getMinutes() === value;
			},

			isActivePeriod (isAM = false) {
				const h = this.value.getHours();
				return isAM ? h < 12 : h >= 12;
			},

			// ---

			setHour (value) {
				value -= 1;

				if (this.isActivePeriod(false))
					value += 13;

				const next = new Date(this.value);
				next.setHours(value);
				this.value = next;

				this.emitInput();
			},

			setMinute (value) {
				value -= 1;

				const next = new Date(this.value);
				next.setMinutes(value);
				this.value = next;

				this.emitInput();
			},

			setPeriod (isAM = false) {
				const isPM = this.isActivePeriod(false);

				const next = new Date(this.value);

				if (isAM && isPM)
					next.setHours(this.value.getHours() - 12);
				else if (!isAM && !isPM)
					next.setHours(this.value.getHours() + 12);

				this.value = next;

				this.emitInput();
			},

		},
	};
</script>

<style module lang="less">
	@import "../../variables";
	@import "Input";

	.wrap {
		display: flex;
	}

	.date {
		flex-grow: 1;

		&:not(:last-child) {
			margin-right: 10px;
		}
	}

	.time {
		width: 100px;
	}

	.times {
		display: flex;

		ul {
			margin: 0;
			padding: 10px 0;
			height: 300px;
			list-style: none;

			overflow: auto;
		}

		button {
			display: block;
			width: 100%;
			padding: 7px 15px;

			font-size: 16px;

			cursor: pointer;
			appearance: none;
			background: none;
			border: none;
			border-radius: 0;
			outline: none;

			transition:
				background-color 0.15s ease,
				color 0.15s ease;

			&:hover,
			&.active {
				color: #fff;
				background-color: @craft-primary;
			}
		}
	}

	.calendar {
		border: none !important;

		> header span {
			transition: background-color 0.15s ease;
		}

		:global .cell {
			transition:
				border-color 0.15s ease,
				background-color 0.15s ease,
				color 0.15s ease;

			&.selected {
				color: #fff;
				background-color: @craft-primary !important;
			}

			&:not(.blank):not(.disabled):hover {
				border-color: @craft-primary !important;
			}

			// Add border radius to the last sunday / 3rd last month / last year
			&:nth-last-child(1).sun,
			&:nth-last-child(2).sun,
			&:nth-last-child(3).sun,
			&:nth-last-child(4).sun,
			&:nth-last-child(5).sun,
			&:nth-last-child(6).sun,
			&:nth-last-child(7).sun,
			&:nth-last-child(3).month,
			&:last-child.year {
				border-radius: 0 0 0 5px;
			}

			// Add border radius to the last saturday (if it's the last child) /
			// last month
			&:last-child.sat,
			&:last-child.month {
				border-radius: 0 0 5px 0;
			}
		}
	}
</style>