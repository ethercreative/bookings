export default ({ className, children, ...props }) => (
	<button {...props} className={`btn ${className ? className : ""}`}>
		{children}
	</button>
);