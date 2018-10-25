<template>
	<button :class="$style.btn">
		{{label}}
	</button>
</template>

<script>
	export default {
		name: 'BookingsButton',
		props: ['label'],
	};
</script>

<style lang="less" module>
	@import "../variables";

	.btn {
		display: inline-block;
		margin: @spacer;
		padding: 10px 20px;

		color: #fff;
		font-size: 14px;
		font-weight: 500;

		appearance: none;
		background: linear-gradient(#da5a47, #cc5643, #bf503f, #b34b3b, #b34b3b, #bf503f);
		background-size: 100% 300%;
		border: none;
		border-radius: 3px;
		cursor: pointer;

		transition: background-position 0.15s ease;

		&:hover {
			background-position: 0 50%;
		}

		&:active {
			background-position: 0 100%;
		}
	}
</style>