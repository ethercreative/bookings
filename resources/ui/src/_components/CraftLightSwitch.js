export default ({ name, on = false, onChange = null }) => (
	<div
		class={`lightswitch ${on ? "on" : ""}`}
		tabIndex="0"
		data-value="1"
		ref={el => {
			if (!onChange)
				return;

			$(el).on('change', () => {
				onChange(el.lastElementChild.value === "1");
			})
		}}
	>
		<div class="lightswitch-container">
			<div class="label on" />
			<div class="handle" />
			<div class="label off" />
		</div>
		<input type="hidden" name={name} value={on && "1"} />
	</div>
);