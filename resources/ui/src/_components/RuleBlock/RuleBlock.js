// @flow

import { Component } from "preact";
import styles from "./RuleBlock.less";
import Frequency from "../../_enums/Frequency";
import connect from "../../_hoc/connect";
import Label from "../Label/Label";
import CraftSelect from "../CraftSelect";
import Row from "../Row/Row";
import CraftDateTime from "../CraftDateTime";
import RRule from "../../_models/RRule";
import CraftLightSwitch from "../CraftLightSwitch";
import debounce from "../../_helpers/debounce";

class RuleBlock extends Component {

	// Properties
	// =========================================================================

	props: {
		rule: RRule;
		isBaseRule: boolean;
		disabled?: boolean;
	};

	static defaultProps = {
		isBaseRule: false,
	};

	// Actions
	// =========================================================================

	updateRule (field, value) {
		const { rule, isBaseRule, dispatch } = this.props;

		if (isBaseRule) {
			dispatch("set:settings.baseRule." + field, value);
		} else {
			dispatch("update:settings.exceptions", {
				id: rule.id,
				field,
				value,
			});
		}
	}

	// Events
	// =========================================================================

	// Events: Fields
	// -------------------------------------------------------------------------

	onFrequencyChange = debounce(freq => {
		this.props.dispatch("updateFrequencies", freq);
	});

	onRepeatsChange = debounce(rep => {
		this.updateRule("repeats", rep);
	});

	onStartDateTimeChange = debounce(dt => {
		this.updateRule("start", dt.date);
	});

	onUntilDateTimeChange = debounce(dt => {
		this.updateRule("until", dt.date);
	});

	onCountChange = debounce(({ target: { value } }) => {
		this.updateRule("count", value|0);
	});

	onIntervalChange = debounce(({ target: { value } }) => {
		this.updateRule("interval", value|0);
	});

	onDurationChange = debounce(({ target: { value } }) => {
		this.updateRule("duration", value|0);
	});

	onBookableChange = debounce(isBookable => {
		this.updateRule("bookable", isBookable);
	});

	onByHourChange = debounce(({ target: { value } }) => {
		this.updateRule("byHour", value);
	});

	onByMinuteChange = debounce(({ target: { value } }) => {
		this.updateRule("byMinute", value);
	});

	onByDayChange = debounce(({ target: { value } }) => {
		this.updateRule("byDay", value);
	});

	// Events: Buttons
	// -------------------------------------------------------------------------

	onDuplicateRuleClick = () => {
		const { rule, dispatch } = this.props;
		dispatch("duplicate:settings.exceptions", rule);
	};

	onDeleteRuleClick = () => {
		const { rule, dispatch } = this.props;
		dispatch("delete:settings.exceptions", rule.id);
	};

	// Render
	// =========================================================================

	render ({ rule }) {
		return (
			<div class={styles.wrap}>
				<div class={styles.block}>
					{this._renderFields()}
					{this._renderFooter()}
				</div>
			</div>
		);
	}

	_renderFields () {
		const { rule, isBaseRule, disabled } = this.props;
		const {
			frequency, repeats, start, until, count, interval, duration,
			byHour, byMinute, byDay
		} = rule;

		return (
			<div class={disabled ? styles.disabled : ""}>
				<Row>
					{isBaseRule ? (
						<Label label="Frequency">
							<CraftSelect onChange={this.onFrequencyChange} disabled={disabled}>
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
					) : (
						<Label label="Bookable">
							<CraftLightSwitch
								on={rule.bookable}
								onChange={this.onBookableChange}
								disabled={disabled}
							/>
						</Label>
					)}

					<Label label="Start Date / Time">
						<CraftDateTime
							showDate
							showTime
							defaultDate={start}
							defaultTime={start}
							onChange={this.onStartDateTimeChange}
							disabled={disabled}
						/>
					</Label>
				</Row>

				<Row>
					<Label label="Repeats">
						<CraftSelect onChange={this.onRepeatsChange} disabled={disabled}>
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
								defaultDate={until}
								defaultTime={until}
								onChange={this.onUntilDateTimeChange}
								disabled={disabled}
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
								disabled={disabled}
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
							disabled={disabled}
						/>
					</Label>

					{isBaseRule && (
						<Label label="Duration">
							<input
								class="text"
								type="number"
								step="1"
								min="1"
								value={duration}
								onInput={this.onDurationChange}
								disabled={disabled}
							/>
						</Label>
					)}
				</Row>

				<Row>
					<Label label="By Hour (0 - 23)">
						<input
							class="text"
							type="text"
							value={byHour}
							onInput={this.onByHourChange}
							disabled={disabled}
						/>
					</Label>

					<Label label="By Minute (0 - 59)">
						<input
							class="text"
							type="text"
							value={byMinute}
							onInput={this.onByMinuteChange}
							disabled={disabled}
						/>
					</Label>
				</Row>

				<Row>
					<Label label="By Day (MO, TU, WE, TH, FR, SA, SU)">
						<input
							class="text"
							type="text"
							value={byDay}
							onInput={this.onByDayChange}
							disabled={disabled}
						/>
					</Label>
				</Row>
			</div>
		);

		// TODO: Add missing BYXXX fields (should be in order of magnitude, large to small)
	}

	_renderFooter () {
		if (this.props.isBaseRule) {
			return (
				<footer class={styles.footer}>
					<span />
					<div>
						<button
							type="button"
							title="Duplicate this rule"
							onClick={this.onDuplicateRuleClick}
						>
							Duplicate
						</button>
					</div>
				</footer>
			)
		}

		return (
			<footer class={styles.footer}>
				<div
					class={["handle", styles.dragHandle].join(" ")}
					title="Move this rule"
				/>

				<div>
					<button
						type="button"
						title="Duplicate this rule"
						onClick={this.onDuplicateRuleClick}
					>
						Duplicate
					</button>

					<button
						type="button"
						title="Delete this rule"
						class={styles.danger}
						onClick={this.onDeleteRuleClick}
					>
						Delete
					</button>
				</div>
			</footer>
		)
	}

}

export default connect()(RuleBlock);