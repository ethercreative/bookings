function onChangeEvent (onChangeCb, e) {
	if (
		e.key
		&& e.key !== "ArrowLeft"
		&& e.key !== "ArrowRight"
		&& e.key !== " "
	) return;

	let target = e.target;

	while (!target.classList.contains("lightswitch"))
		target = target.parentNode;

	// Wait for DOM to be slowly updated by jQuery
	setTimeout(() => {
		onChangeCb(target.lastElementChild.value === "1");
	}, 15);
}

// FIXME: We lose sync when the light switch is dragged :(

export default ({ name, on = false, onChange = null }) => (
	<div
		class={`lightswitch ${on ? "on" : ""}`}
		tabIndex="0"
		data-value="1"
		onMouseDown={onChangeEvent.bind(this, onChange)}
		onKeyDown={onChangeEvent.bind(this, onChange)}
	>
		<div class="lightswitch-container">
			<div class="label on" />
			<div class="handle" />
			<div class="label off" />
		</div>
		<input type="hidden" name={name} value={on && "1"} />
	</div>
);