import { Component } from "preact";
import styles from "./RulesModal.less";
import Modal from "../_components/Modal/Modal";
import RuleBlock from "../_components/RuleBlock/RuleBlock";
import connect from "../_hoc/connect";
import Sortable from "../_components/Sortable";

class RulesModal extends Component {

	// Properties
	// =========================================================================

	props: {
		isOpen: boolean;
		onRequestClose: Function;
	};

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

	// Render
	// =========================================================================

	render ({ isOpen, onRequestClose }) {
		return (
			<Modal isOpen={isOpen} onRequestClose={onRequestClose}>
				{this._renderSidebar()}
			</Modal>
		);
	}

	_renderSidebar () {
		const { baseRule, exceptions } = this.props;

		return (
			<aside class={styles.sidebar}>
				<header class={styles.sidebarHeader}>
					<h2>Bookable Rules</h2>
					<p>Add rules below to either add bookable space, or
						remove it from the primary booking window</p>
				</header>

				<div class={styles.rulesWrap}>
					<div class={styles.rules}>
						<RuleBlock rule={baseRule} isBaseRule />

						<Sortable onSort={this.onSortRules}>
							{exceptions.map(ex => (
								<RuleBlock key={ex.id} rule={ex}/>
							))}
						</Sortable>
					</div>
				</div>

				<button
					type="button"
					class={["btn", styles.newRule].join(" ")}
					onClick={this.onAddRuleClick}
				>
					Add new rule
				</button>
			</aside>
		);
	}

}

export default connect(({ settings: { baseRule, exceptions } }) => ({
	baseRule,
	exceptions,
}))(RulesModal);