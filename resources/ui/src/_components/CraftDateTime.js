// @flow
import { Component } from "preact";
import connect from "../_hoc/connect";
import formatDate from "../_helpers/formatDate";
import jQuizzle from "../_helpers/jQuizzle";

class CraftDateTime extends Component {

	// Properties
	// =========================================================================

	props: {
		showDate?: boolean;
		showTime?: boolean;
		defaultDate?: Date | string;
		defaultTime?: Date | string;
		onChange?: Function;
		disabled?: boolean;
	};

	dateInput = null;
	timeInput = null;

	constructor (props) {
		super(props);

		const value = new Date()
			, d = this._getDefaultDate(props)
			, t = this._getDefaultTime(props);

		if (d) {
			value.setFullYear(d.getFullYear());
			value.setMonth(d.getMonth());
			value.setDate(d.getDate());
		}

		if (t) {
			value.setHours(t.getHours());
			value.setMinutes(t.getMinutes());
		}

		value.setSeconds(0);
		value.setMilliseconds(0);

		this.state = { value };
	}

	// Preact
	// =========================================================================

	shouldComponentUpdate = () => false;

	componentDidMount () {
		if (this.props.disabled)
			return;

		jQuizzle($ => {

			if (this.dateInput) {
				const $datePicker = $(this.dateInput);

				$datePicker.datepicker($.extend({
					defaultDate: this._getDefaultDate()
				}, window.Craft.datepickerOptions));

				$datePicker.on("change", this.onDateChange);
			}

			if (this.timeInput) {
				const $timePicker = $(this.timeInput);

				$timePicker.timepicker($.extend({
					step: 5,
				}, window.Craft.timepickerOptions));

				if (this.props.defaultTime) {
					const t = this._getDefaultTime();
					$timePicker.timepicker(
						"setTime",
						t.getHours() * 3600
						+ t.getMinutes() * 60
						+ t.getSeconds()
					);
				}

				$timePicker.on("change", this.onTimeChange);
			}

		});
	}

	// Events
	// =========================================================================

	onDateChange = ({ target: { value } }) => {
		const d = new Date(value);

		const nextValue = new Date(this.state.value);
		nextValue.setFullYear(d.getFullYear());
		nextValue.setMonth(d.getMonth());
		nextValue.setDate(d.getDate());

		this.setState({ value: nextValue }, this.onChange);
	};

	onTimeChange = ({ target: { value } }) => {
		const t = new Date(`1/1/1970 ${value}`);

		const nextValue = new Date(this.state.value);
		nextValue.setHours(t.getHours());
		nextValue.setMinutes(t.getMinutes());

		this.setState({ value: nextValue }, this.onChange);
	};

	onChange = () => {
		const { onChange } = this.props;

		if (!onChange)
			return;

		onChange({
			date: this.state.value,
			timezone: this.props.timezone,
		});
	};

	// Render
	// =========================================================================

	render ({ showDate, showTime, dateFormat }) {
		if (showDate && !showTime)
			return this._renderDate();

		if (showTime && !showDate)
			return this._renderTime();

		return (
			<div class="datetimewrapper">
				{this._renderDate()}
				{"\t"}
				{this._renderTime()}
			</div>
		);
	}

	_renderDate () {
		const { dateFormat, disabled } = this.props;

		const value = this.props.defaultDate ? formatDate(this._getDefaultDate(), dateFormat.date) : "";

		return (
			<div class="datewrapper">
				<input
					class="text"
					size="10"
					value={value}
					autocomplete="off"
					placeholder=" "
					type="text"
					ref={el => { this.dateInput = el }}
					onChange={this.onDateChange}
					disabled={disabled}
				/>
				<div data-icon="date"/>
			</div>
		);
	}

	_renderTime () {
		const { dateFormat, disabled } = this.props;

		const value = this.props.defaultTime ? formatDate(this._getDefaultTime(), dateFormat.time) : "";

		return (
			<div class="timewrapper">
				<input
					class="text ui-timepicker-input"
					size="10"
					value={value}
					autocomplete="off"
					placeholder=" "
					type="text"
					ref={el => { this.timeInput = el }}
					onChange={this.onTimeChange}
					disabled={disabled}
				/>
				<div data-icon="time"/>
			</div>
		);
	}

	// Helpers
	// =========================================================================

	_defaultDate = null;
	_getDefaultDate (props = this.props) {
		if (this._defaultDate)
			return this._defaultDate;

		const { defaultDate } = props;

		this._defaultDate = defaultDate
			? defaultDate instanceof Date
				? defaultDate
				: new Date(defaultDate)
			: new Date();

		return this._defaultDate;
	}

	_defaultTime = null;
	_getDefaultTime (props = this.props) {
		if (this._defaultTime)
			return this._defaultTime;

		const { defaultTime } = props;

		this._defaultTime = defaultTime
			? defaultTime instanceof Date
				? defaultTime
				: new Date(defaultTime)
			: new Date();

		return this._defaultTime;
	}

}

export default connect(({ dateFormat, timezone }) => ({
	dateFormat,
	timezone,
}))(CraftDateTime);