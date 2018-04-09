import findTabbable from './tabbable';

class FocusManager {

	previouslyFocused = null;
	element = null;
	needToFocus = false;

	// Setup & Teardown
	// =========================================================================

	setup (element) {
		this.element = element;
		this.previouslyFocused = document.activeElement;

		window.addEventListener("blur", this.onBlur, false);
		document.addEventListener("focus", this.onFocus, true);
		document.addEventListener("keydown", this.onKeyDown);

		(findTabbable(this.element)[0] || this.element).focus();
	}

	destroy () {
		this.element = null;

		if (this.previouslyFocused) {
			try {
				this.previouslyFocused.focus();
			} catch (_) {/**/}
		}

		window.removeEventListener("blur", this.onBlur);
		document.removeEventListener("focus", this.onFocus);
		document.removeEventListener("keydown", this.onKeyDown);
	}

	// Events
	// =========================================================================

	onFocus = () => {
		if (!this.needToFocus) return;

		this.needToFocus = false;

		if (!this.element || this.element.contains(document.activeElement))
			return;

		(findTabbable(this.element)[0] || this.element).focus();
	};

	onBlur = () => {
		this.needToFocus = true;
	};

	onKeyDown = e => {
		if (e.keyCode === 9)
			this.scopeTab(e);
	};

	// Helpers
	// =========================================================================

	scopeTab (e) {
		const tabbable = findTabbable(this.element);

		if (!tabbable.length) {
			e.preventDefault();
			return;
		}

		const finalTabbable = tabbable[e.shiftKey ? 0 : tabbable.length - 1];

		const leavingFinalTabbable = (
			finalTabbable === document.activeElement ||
			// handle immediate shift+tab after opening with mouse
			this.element === document.activeElement
		);

		if (!leavingFinalTabbable) return;
		e.preventDefault();

		const target = tabbable[e.shiftKey ? tabbable.length - 1 : 0];
		target.focus();
	}

}

export default new FocusManager();