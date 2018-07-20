export default ({ children, onChange }) => (
	<div class="select">
		<select onChange={e => { onChange && onChange(e.target.value); }}>
			{children}
		</select>
	</div>
);