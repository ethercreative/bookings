<template>
	<button
		:type="type"
		:value="value"
		:name="name"
		:disabled="disabled"
		:title="title"
		:class="$style.button"
		@click="$emit('click', $event)"
	>
		<slot></slot>
	</button>
</template>

<script>
	export default {
		name: "Button",
		props: {
			type: {
				type: String,
				default: "button",
			},
			value: null,
			name: String,
			disabled: Boolean,
			title: String,
		},
	}
</script>

<style module lang="less">
	@import "../../variables";

	.button {
		display: inline-block;
		padding: 6px 15px;

		color: #FFFFFF;
		font-size: 14px;
		text-align: center;

		appearance: none;
		background: @craft-primary linear-gradient(
			-180deg,
			rgba(0,0,0,0.00) 0%,
			rgba(1,1,1,0.2) 100%
		);
		background-size: 100% 300%;
		background-position-y: 50%;
		border: 1px solid rgba(0,0,0,0.1);
		border-radius: 3px;
		outline: none;

		cursor: pointer;
		transition: background-position 0.2s ease, border-color 0.2s ease;

		&:hover {
			background-position-y: -1px;
		}

		&:active {
			background-position-y: calc(~"100% + 1px");
		}
	}
</style>