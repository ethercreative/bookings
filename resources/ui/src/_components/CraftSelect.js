export default ({ children, onChange, disabled }) => (
	<div class="select">
		<select onChange={e => { onChange && onChange(e.target.value); }} disabled={disabled}>
			{children}
		</select>
	</div>
);