export default ({ label, instructions = null, className = "", children }) => (
	<div class={`field ${className}`}>
		<div class="heading">
			<label>{label}</label>
			{instructions && (
				<div class="instructions">
					<p>{instructions}</p>
				</div>
			)}
		</div>
		<div class="input ltr">
			{children}
		</div>
	</div>
);