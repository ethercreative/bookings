import styles from "./Row.less";

export default ({ children }) => (
	<div class={styles.row}>
		{children}
	</div>
);