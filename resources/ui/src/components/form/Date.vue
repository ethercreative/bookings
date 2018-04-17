<template>
	<label>
		<input
			type="text"
			readonly
			@focus="openModal"
			ref="input"
			:value="formatDate(value, 'd/m/Y')"
			:class="$style.input"
		/>

		<Modal
			:open="isOpen"
			:on-request-close="onRequestClose"
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
</template>

<script>
	import DatePicker from "vuejs-datepicker";
	import formatDate from "../../helpers/formatDate";
	import Modal from "../Modal";

	export default {
		name: "Date",
		components: { Modal, DatePicker },

		data () {
			return {
				isOpen: false,
				value: new Date(),
			};
		},

		methods: {

			openModal () {
				this.$refs.input.blur();
				this.isOpen = true;
			},

			onRequestClose () {
				this.isOpen = false;
			},

			onInput (value) {
				this.value = value;
				this.$emit("input", value);
			},

			formatDate,

		},
	};
</script>

<style module lang="less">
	@import "../../variables";
	@import "Input";

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