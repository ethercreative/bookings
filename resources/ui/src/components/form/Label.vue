<template>
	<component :is="elem" :class="[$style.label, {[$style.shrink]: shrink}]">
		<span>
			{{label}}
			<span v-if="required">*</span>
		</span>
		<slot></slot>
	</component>
</template>

<script>
	export default {
		name: "Label",
		props: {
			label: String,
			required: Boolean,
			shrink: Boolean,
			elem: {
				type: String,
				default: "label",
			}
		},
	}
</script>

<style module lang="less">
	@import "../../variables";

	.label {
		display: inline-block;

		> span {
			display: block;
			margin-bottom: 5px;

			color: #8C97B2;
			font-size: 10px;
			font-weight: bold;
			letter-spacing: 0.5px;
			text-transform: uppercase;

			opacity: 0.7;

			span {
				color: @craft-primary;
			}
		}

		&.shrink {
			flex-shrink: 2;
		}
	}
</style>