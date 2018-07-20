import styles from "./Label.less";

export default ({ label, children }) => (
	<label class={styles.label}>
		<span>{label}</span>
		{children}
	</label>
);