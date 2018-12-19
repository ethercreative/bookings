<script type="text/jsx">
import Vue from 'vue';
import Component from 'vue-class-component';
import formatDate from '../helpers/formatDate';

@Component({
	props: {
		activeDate: {
			type: Date,
			default: new Date(),
		},
		activeDay: {
			type: Number,
		},
		availability: {
			type: Object,
		},
		includeFullyBooked: {
			type: Boolean,
			default: false,
		},
		whenDayClick: {
			type: Function,
		},
	},
	watch: {
		activeDate () {
			this.cacheDays();
		},
	},
})
export default class MiniCalendar extends Vue {

	// Properties
	// =========================================================================

	offset = 0;
	days = 0;
	dateTemplate = null;
	activeDate = null;
	months = [];
	activeMonthIndex = 0;

	// Getters
	// =========================================================================

	get hasNextMonth () {
		return this.months.length - 1 > this.activeMonthIndex;
	}

	get hasPrevMonth () {
		return this.activeMonthIndex > 0;
	}

	// Vue
	// =========================================================================

	beforeMount () {
		this.activeDate = new Date(this.$props.activeDate);
		this.activeDate.setDate(1);
		this.cacheMonths();
		this.cacheDays();
	}

	// Actions
	// =========================================================================

	updateDays () {
		this.activeDate = this.months[this.activeMonthIndex];
		this.cacheDays();
	}

	// Events
	// =========================================================================

	onDateClick (date, e) {
		e.preventDefault();
		this.$props.whenDayClick && this.$props.whenDayClick(date);
	}

	onPrevMonthClick () {
		this.activeMonthIndex--;
		this.updateDays();
	}

	onNextMonthClick () {
		this.activeMonthIndex++;
		this.updateDays();
	}

	// Render
	// =========================================================================

	render () {
		return (
			<div class={this.$style.calendar}>
				<header class={this.$style.header}>
					{this.hasPrevMonth ? (
						<button type="button" onClick={this.onPrevMonthClick}>
							{'<'}
						</button>
					) : <span />}
					{formatDate(this.$props.activeDate, 'F Y')}
					{this.hasNextMonth ? (
						<button type="button" onClick={this.onNextMonthClick}>
							{'>'}
						</button>
					) : <span />}
				</header>

				<ul class={[this.$style.grid, this.$style.week]}>
					<li>M</li>
					<li>T</li>
					<li>W</li>
					<li>T</li>
					<li>F</li>
					<li>S</li>
					<li>S</li>
				</ul>

				<div class={[this.$style.grid, this.$style.days]}>
					{this.offset > 0 && (
						<div style={{ gridColumn: `span ${this.offset}` }}/>
					)}
					{Array.from({ length: this.days }, this._renderDay)}
				</div>
			</div>
		);
	}

	_renderDay (_, day) {
		day++; // 0 index to 1

		const availability = this.$props.availability;
		const minAvailability = this.$props.includeFullyBooked ? -1 : 0;
		const date = this.dateTemplate.replace('[]', (day + '').padStart(2, '0'));

		if (availability && availability.hasOwnProperty(date) && availability[date] > minAvailability) {
			return (
				<button
					key={day}
					onClick={this.onDateClick.bind(this, date)}
					class={{
						[this.$style.active]: date === this.$props.activeDay
					}}
				>
					{day}
				</button>
			);
		}

		return (
			<span key={day}>
				{day}
			</span>
		);
	}

	// Helpers
	// =========================================================================

	cacheMonths () {
		if (!this.$props.availability)
			return;

		const keys = Object.keys(this.$props.availability);

		const first = new Date(keys[0])
			, last  = new Date(keys[keys.length - 1]);

		first.setDate(1);
		last.setDate(1);

		let d = first;

		do {
			this.months.push(d);
			d = new Date(d);
			d.setMonth(d.getMonth() + 1);
		} while (d.getTime() <= last.getTime());
	}

	cacheDays () {
		const start = new Date(this.activeDate)
			, end   = new Date(this.activeDate);

		start.setDate(1);
		end.setMonth(end.getMonth() + 1);
		end.setDate(0);

		let offset = start.getDay() - 1;
		if (offset < 0) offset = 6;
		this.offset = offset;

		this.days = end.getDate();

		this.dateTemplate = formatDate(
			start,
			'Y-m-[] 00:00:00'
		);
	}

}
</script>

<style lang="less" module>
	@import '../variables';

	.calendar {
		margin-bottom: -@spacer*2;
		padding: @spacer/2;
		float: right;

		background-color: #fff;
		box-shadow: 0 6px 15px 0 rgba(93, 130, 170, 0.21);
		border-radius: 7px;
	}

	.header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: @spacer/2;

		color: @text-color;
		font-size: 14px;
		font-family: @font-family;
		letter-spacing: 0;
		text-align: center;

		button,
		span {
			display: inline-block;
			width: 24px;
			height: 24px;
		}

		button {
			color: #758EA1;
			font-weight: bold;

			appearance: none;
			background: none;
			border: none;
			cursor: pointer;
		}
	}

	.grid {
		display: grid;
		grid-gap: 5px;
		grid-template-columns: repeat(7, 1fr);
	}

	.week {
		margin-bottom: @spacer/2;

		color: #758EA1;
		font-family: @font-family;
		font-size: 8.4px;
		letter-spacing: -0.2px;
		text-align: center;
		line-height: 7px;

		list-style: none;
	}

	.offset {
		visibility: hidden;
	}

	.days {
		button,
		span {
			display: block;
			width: 27px;
			height: 27px;
			padding: 0;

			color: #97A2AE;
			font-size: 11px;
			font-family: @font-family;
			letter-spacing: 0;
			text-align: center;
			line-height: 27px;

			background-color: #F1F5F8;
			border-radius: 7px;
		}

		button {
			color: #29A601;

			appearance: none;
			background-color: #EAF6E6;
			border: none;
			cursor: pointer;

			&.active {
				color: #fff;
				background-color: #097DFF;
			}
		}
	}
</style>