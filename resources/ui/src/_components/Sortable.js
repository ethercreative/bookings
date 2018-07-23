// @flow

import { Component } from "preact";
import jQuizzle from "../_helpers/jQuizzle";

export default class Sortable extends Component {

	// Properties
	// =========================================================================

	props: {
		onSort?: Function;
	};

	wrap = null;
	sort = null;

	// Preact
	// =========================================================================

	componentDidMount () {
		const { onSort } = this.props;

		jQuizzle($ => {
			this.sort = new window.Garnish.DragSort($(this.wrap.children), {
				container: $(this.wrap),
				axis: window.Garnish.Y_AXIS,
				moveTargetItemToFront: true,
				handle: ".handle",
				onSortChange: () => {
					onSort && onSort(this.wrap.children);
				},
			});
		});
	}

	componentDidUpdate () {
		jQuizzle($ => {
			if (this.sort) {
				this.sort.removeAllItems();
				this.sort.addItems($(this.wrap.children));
			}
		});
	}

	// Render
	// =========================================================================

	render ({ children }) {
		return (
			<div ref={el => { this.wrap = el }}>
				{children}
			</div>
		)
	}

}