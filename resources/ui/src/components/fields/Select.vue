<template>
	<span :class="$style.select">
		<select
			:name="name"
			:value="value"
			@change="$emit('input', $event.target.value)"
		>
			<slot></slot>
		</select>
	</span>
</template>

<script>
	export default {
		name: "Select",
		props: ["name", "value"],
	}
</script>

<style module lang="less">
	@import "../../variables";

	.select {
		position: relative;
		display: inline-block;

		&:before {
			content: '';
			position: absolute;
			top: 50%;
			right: 17px;

			width: 8px;
			height: 8px;
			margin-top: -7px;

			border-bottom: 1px solid @border-dark;
			border-right: 1px solid @border-dark;

			pointer-events: none;
			transform: rotate(45deg);
		}

		select {
			display: inline-block;
			padding: 9px 35px 9px 15px;

			color: @color;
			font-size: 14px;
			line-height: normal;

			appearance: none;
			background: none;
			border: 1px solid @border;
			border-radius: 3px;
			outline: none;

			transition: border-color 0.15s ease;

			&:hover,
			&:focus {
				border-color: @border-dark;
			}
		}

		&:hover select {
			border-color: @border-dark;
		}
	}
</style>

