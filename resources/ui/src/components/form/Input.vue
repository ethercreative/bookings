<template>
	<input
		:class="$style.input"
		:type="type"
		:accept="accept"
		:autocomplete="autocomplete"
		:autofocus="autofocus"
		:disabled="disabled"
		:max="max"
		:maxlength="maxlength"
		:min="min"
		:minlength="minlength"
		:placeholder="placeholder"
		:readonly="readonly"
		:required="required"
		:step="step"
		:value="value"
		@input="$emit('input', $event.target.value)"
		@blur="onBlur"
	/>
</template>

<script>
	export default {
		name: "Input",
		props: {
			type: {
				type: String,
				required: true,
			},

			accept: String,
			autocomplete: String,
			autofocus: String,
			disabled: Boolean,
			max: Number,
			maxlength: Number,
			min: Number,
			minlength: Number,
			placeholder: String,
			readonly: Boolean,
			required: Boolean,
			step: Number,
			value: null,
		},

		methods: {
			onBlur: function (e) {
				if (this.type !== "number")
					return;

				const v = +e.target.value;

				if (this.min && v < this.min)
					this.updateValue(e.target, this.min);

				if (this.max && v > this.max)
					this.updateValue(e.target, this.max);
			},

			updateValue: function (el, v) {
				el.value = v;
				this.$emit("input", v);
			},
		}
	}
</script>

<style module lang="less">
	@import "../../variables";

	.input {
		display: block;
		width: 100%;
		padding: 9px 0 9px 0;

		color: @color;
		font-size: 14px;
		line-height: normal;
		text-indent: 15px;

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
</style>