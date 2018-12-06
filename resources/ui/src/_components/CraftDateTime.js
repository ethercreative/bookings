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
			value.setUTCFullYear(d.getUTCFullYear());
			value.setUTCMonth(d.getUTCMonth());
			value.setUTCDate(d.getUTCDate());
		}

		if (t) {
			value.setUTCHours(t.getUTCHours());
			value.setUTCMinutes(t.getUTCMinutes());
		}

		value.setUTCSeconds(0);
		value.setUTCMilliseconds(0);

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

				$datePicker.on("change", this.onDateChange.bind(this, $datePicker));
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
						t.getUTCHours() * 3600
						+ t.getUTCMinutes() * 60
						+ t.getUTCSeconds()
					);
				}

				$timePicker.on("change", this.onTimeChange);
			}

		});
	}

	// Events
	// =========================================================================

	onDateChange = $datePicker => {
		const d = $datePicker.datepicker("getDate");

		const nextValue = new Date(this.state.value);
		nextValue.setUTCFullYear(d.getFullYear());
		nextValue.setUTCMonth(d.getMonth());
		nextValue.setUTCDate(d.getDate());

		this.setState({ value: nextValue }, this.onChange);
	};

	onTimeChange = ({ target: { value } }) => {
		const t = new Date(`1/1/1970 ${value}`);

		const nextValue = new Date(this.state.value);
		nextValue.setUTCHours(t.getHours());
		nextValue.setUTCMinutes(t.getMinutes());

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