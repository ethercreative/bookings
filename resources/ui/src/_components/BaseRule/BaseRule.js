import { Component } from "preact";
import Frequency from "../../_enums/Frequency";
import connect from "../../_hoc/connect";
import Label from "../Label/Label";
import CraftSelect from "../CraftSelect";
import Row from "../Row/Row";
import CraftDateTime from "../CraftDateTime";

class BaseRule extends Component {

	// Events
	// =========================================================================

	onFrequencyChange = freq => {
		this.props.dispatch("set:settings.baseRule.frequency", freq);
	};

	onRepeatsChange = rep => {
		this.props.dispatch("set:settings.baseRule.repeats", rep);
	};

	onStartDateTimeChange = dt => {
		this.props.dispatch("set:settings.baseRule.start", dt);
	};

	onUntilDateTimeChange = dt => {
		this.props.dispatch("set:settings.baseRule.until", dt);
	};

	onCountChange = ({ target: { value } }) => {
		this.props.dispatch("set:settings.baseRule.count", value|0);
	};

	onIntervalChange = ({ target: { value } }) => {
		this.props.dispatch("set:settings.baseRule.interval", value|0);
	};

	onDurationChange = ({ target: { value } }) => {
		this.props.dispatch("set:settings.baseRule.duration", value|0);
	};

	// Render
	// =========================================================================

	render ({ baseRule }) {
		const {
			frequency, repeats, start, until, count, interval, duration
		} = baseRule;

		return (
			<div>
				<Row>
					<Label label="Frequency">
						<CraftSelect onChange={this.onFrequencyChange}>
							{Frequency.asKeyValueArray().map(o => (
								<option
									value={o.value}
									selected={o.value === frequency}
								>
									{o.key}
								</option>
							))}
						</CraftSelect>
					</Label>

					<Label label="Start Date / Time">
						<CraftDateTime
							showDate
							showTime
							defaultDate={start.date}
							defaultTime={start.date}
							onChange={this.onStartDateTimeChange}
						/>
					</Label>
				</Row>

				<Row>
					<Label label="Repeats">
						<CraftSelect onChange={this.onRepeatsChange}>
							{[
								{ label: "Until", value: "until" },
								{ label: "# Times", value: "count" },
								{ label: "Forever", value: "forever" },
							].map(o => (
								<option
									value={o.value}
									selected={o.value === repeats}
								>
									{o.label}
								</option>
							))}
						</CraftSelect>
					</Label>

					{repeats === "until" && (
						<Label label="End Date / Time">
							<CraftDateTime
								showDate
								showTime
								defaultDate={until.date}
								defaultTime={until.date}
								onChange={this.onUntilDateTimeChange}
							/>
						</Label>
					)}

					{repeats === "count" && (
						<Label label="Count">
							<input
								class="text"
								type="number"
								step="1"
								min="0"
								value={count}
								onInput={this.onCountChange}
							/>
						</Label>
					)}
				</Row>

				<Row>
					<Label label="Interval">
						<input
							class="text"
							type="number"
							step="1"
							min="0"
							value={interval}
							onInput={this.onIntervalChange}
						/>
					</Label>

					<Label label="Duration">
						<input
							class="text"
							type="number"
							step="1"
							min="1"
							value={duration}
							onInput={this.onDurationChange}
						/>
					</Label>
				</Row>
			</div>
		);
	}

}

export default connect(({ settings: { baseRule } }) => ({
	baseRule,
}))(BaseRule);