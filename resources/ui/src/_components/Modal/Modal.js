// @flow

import { Component } from "preact";
import styles from "./Modal.less";
import Portal from "preact-portal";

export default class Modal extends Component {

	// Properties
	// =========================================================================

	props: {
		isOpen: boolean;
		onRequestClose: Function;
	};

	static defaultProps = {
		isOpen: false,
	};

	state = {
		isOpen: false,
		willOpen: false,
		willClose: false,
	};

	shade = null;

	willOpenTO = null;

	// Preact
	// =========================================================================

	componentDidMount () {
		document.addEventListener("keydown", this.onDocumentKeyDown, true);
	}

	componentWillUnmount () {
		document.removeEventListener("keydown", this.onDocumentKeyDown, true);
		this.willOpenTO && clearTimeout(this.willOpenTO);
	}

	componentWillReceiveProps (nextProps) {
		if (this.props.isOpen === nextProps.isOpen)
			return;

		const nextState = {
			willClose: nextProps.isOpen === false,
		};

		if (this.props.isOpen === false && nextProps.isOpen === true) {
			nextState.isOpen = true;
			nextState.willOpen = true;

			this.willOpenTO = setTimeout(() => {
				this.setState({ willOpen: false });
			}, 15);
		}

		this.setState(nextState);
	}

	componentWillUpdate ({ isOpen }) {
		if (this.shade && !isOpen)
			this.shade.removeEventListener("transitionend", this.onCloseTransitionComplete, true);
	}

	componentDidUpdate () {
		if (this.shade)
			this.shade.addEventListener("transitionend", this.onCloseTransitionComplete, true);
	}

	// Events
	// =========================================================================

	onDocumentKeyDown = e => {
		if (!this.props.isOpen)
			return;

		if (e.key === "Escape")
			this.props.onRequestClose();
	};

	onShadeClick = ({ target }) => {
		if (target !== this.shade)
			return;

		this.props.onRequestClose();
	};

	onCloseTransitionComplete = () => {
		if (this.props.isOpen === true)
			return;

		this.setState({
			isOpen: false,
			willClose: false,
		});
	};

	// Render
	// =========================================================================

	render ({ children }, { isOpen, willOpen, willClose }) {
		const shadeCls = ["modal-shade", styles.overlay];

		if (isOpen && !willClose && !willOpen)
			shadeCls.push(styles.open);

		return isOpen ? (
			<Portal into={document.body}>
				<div
					class={shadeCls.join(" ")}
					ref={el => { this.shade = el; }}
					onClick={this.onShadeClick}
				>
					<div class={["modal", styles.modal].join(" ")}>
						{children}
					</div>
				</div>
			</Portal>
		) : null;
	}

}