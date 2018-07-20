import { Component } from "preact";
import Frequency from "../../_enums/Frequency";
import connect from "../../_hoc/connect";
import Label from "../Label/Label";
import CraftSelect from "../CraftSelect";
import Row from "../Row/Row";

class BaseRule extends Component {

	// Events
	// =========================================================================

	onFrequencyChange = freq => {
		this.props.dispatch("set:settings.baseRule.frequency", freq);
	};

	onRepeatsChange = rep => {
		this.props.dispatch("set:settings.baseRule.repeats", rep);
	};

	// Render
	// =========================================================================

	render ({ baseRule }) {
		const { frequency, repeats } = baseRule;

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
						[TODO: Date/time Input]
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
				</Row>
			</div>
		);
	}

}

export default connect(({ settings: { baseRule } }) => ({
	baseRule,
}))(BaseRule);