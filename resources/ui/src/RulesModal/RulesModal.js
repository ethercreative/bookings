import { Component } from "preact";
import styles from "./RulesModal.less";
import Modal from "../_components/Modal/Modal";
import RuleBlock from "../_components/RuleBlock/RuleBlock";
import connect from "../_hoc/connect";
import Sortable from "../_components/Sortable";
import Frequency from "../_enums/Frequency";
import CraftButton from "../_components/CraftButton";
import Week from "../_components/calendar/Week/Week";
import Day from "../_components/calendar/Day/Day";

const CALENDAR_TABS = [
	{
		label: "Day",
		handle: "day",
		frequencies: [Frequency.Minutely]
	},
	{
		label: "Week",
		handle: "week",
		frequencies: [Frequency.Hourly]
	},
	{
		label: "Month",
		handle: "month",
		frequencies: [Frequency.Daily, Frequency.Weekly]
	},
	{
		label: "Year",
		handle: "year",
		frequencies: [Frequency.Monthly, Frequency.Yearly]
	},
];

const TAB_BY_FREQ = {
	[Frequency.Minutely]: "day",
	[Frequency.Hourly]: "week",
	[Frequency.Daily]: "month",
	[Frequency.Weekly]: "month",
	[Frequency.Monthly]: "year",
	[Frequency.Yearly]: "year",
};

class RulesModal extends Component {

	// Properties
	// =========================================================================

	props: {
		isOpen: boolean;
		onRequestClose: Function;
	};

	state = {
		activeView: "week",
	};

	// Preact
	// =========================================================================

	componentWillReceiveProps (nextProps) {
		const activeView = TAB_BY_FREQ[nextProps.baseRule.frequency];

		if (this.state.activeView !== activeView)
			this.setState({ activeView });
	}

	// Events
	// =========================================================================

	onAddRuleClick = () => {
		this.props.dispatch("new:settings.exceptions", null);
	};

	onSortRules = sortedChildren => {
		const sortedIds = [];

		for (let i = 0, l = sortedChildren.length; i < l; ++i)
			sortedIds.push(sortedChildren[i]._component.props.rule.id);

		this.props.dispatch("sort:settings.exceptions", sortedIds);
	};

	onTabClick = activeView => {
		this.setState({ activeView });
	};

	onCloseClick = () => {
		this.props.onRequestClose();
	};

	// Render
	// =========================================================================

	render ({ isOpen, onRequestClose }) {
		return (
			<Modal isOpen={isOpen} onRequestClose={onRequestClose}>
				{this._renderSidebar()}
				{this._renderMain()}
			</Modal>
		);
	}

	_renderSidebar () {
		const { baseRule, exceptions, hasAnyBookings } = this.props;

		return (
			<aside class={styles.sidebar}>
				<header class={styles.sidebarHeader}>
					<h2>Bookable Rules</h2>
					<p>Add rules below to either add bookable space, or
						remove it from the primary booking window</p>
				</header>

				<div class={styles.rulesWrap}>
					<div class={styles.rules}>
						{hasAnyBookings ? (
							<div class={[styles.notice, styles.warning].join(" ")}>
								<strong>Notice:</strong> You are no longer able
								to edit the primary rule as this element has
								bookings.
							</div>
						) : (
							<div class={styles.notice}>
								<strong>Notice:</strong> You will be unable to
								edit the primary rule once the first booking
								has been placed.
							</div>
						)}

						<RuleBlock
							isBaseRule
							rule={baseRule}
							disabled={hasAnyBookings}
						/>

						<hr/>

						{exceptions.length === 0 ? (
							<div class={styles.empty}>
								You haven't added any exceptions yet
							</div>
						) : (
							<Sortable onSort={this.onSortRules}>
								{exceptions.map(ex => (
									<RuleBlock key={ex.id} rule={ex}/>
								))}
							</Sortable>
						)}
					</div>
				</div>

				<CraftButton
					className={["submit add icon", styles.newRule].join(" ")}
					onClick={this.onAddRuleClick}
				>
					Add Exception
				</CraftButton>
			</aside>
		);
	}

	_renderMain () {
		const { activeView } = this.state;

		return (
			<div class={styles.main}>
				<header class={styles.header}>
					<ul class={styles.tabs}>
						{CALENDAR_TABS.map(t => (
							<li>
								<button
									type="button"
									class={activeView === t.handle ? styles.active : ""}
									disabled={!this._allowedView(t.frequencies)}
									onClick={this.onTabClick.bind(this, t.handle)}
								>
									{t.label}
								</button>
							</li>
						))}
					</ul>

					<CraftButton
						className={styles.close}
						onClick={this.onCloseClick}
					>
						Close
					</CraftButton>
				</header>

				{activeView === "day" && <Day />}
				{activeView === "week" && <Week />}
			</div>
		);
	}

	// Helpers
	// =========================================================================

	_allowedView (freqs) {
		return freqs.indexOf(this.props.baseRule.frequency) !== -1;
	}

}

export default connect(({ settings: { baseRule, exceptions }, hasAnyBookings }) => ({
	baseRule,
	exceptions,
	hasAnyBookings,
}))(RulesModal);