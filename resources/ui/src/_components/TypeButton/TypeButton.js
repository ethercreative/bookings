import styles from "./TypeButton.less";

export default function TypeButton ({ active, type, name, instructions, icon, onClick, disabled = false }) {
	const cls = [styles.type];

	if (active)
		cls.push(styles.active);

	if (disabled)
		cls.push(styles.disabled);

	return (
		<button
			type="button"
			class={cls.join(" ")}
			onClick={e => onClick(e, type)}
			disabled={disabled}
		>
			{icon}
			<strong>{name}</strong>
			<span>{instructions}</span>
		</button>
	);
}