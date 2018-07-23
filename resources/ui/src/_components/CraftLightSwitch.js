import jQuizzle from "../_helpers/jQuizzle";

export default ({ name, on = false, onChange = null }) => (
	<div
		class={`lightswitch ${on ? "on" : ""}`}
		tabIndex="0"
		data-value="1"
		ref={el => {
			if (!el || !onChange || el._clse)
				return;

			el._clse = true;

			jQuizzle($ => {
				const $lightSwitch = $(el);

				$lightSwitch.lightswitch();

				$lightSwitch.on('change', () => {
					onChange(el.lastElementChild.value === "1");
				});
			});
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