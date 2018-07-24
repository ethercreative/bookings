export default ({ className, children, type, ...props }) => (
	<button {...props} type={type || "button"} className={`btn ${className ? className : ""}`}>
		{children}
	</button>
);