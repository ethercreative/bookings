// @flow

type Props = {
	label: string;
	instructions?: string;
	className?: string;
};

export default ({ label, instructions = null, className = "", children }:Props) => (
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