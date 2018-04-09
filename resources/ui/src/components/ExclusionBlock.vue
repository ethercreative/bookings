<template>
	<div :class="[$style.exclusionBlockWrap, { [$style.disabled]: disabled }]">
		<div :class="$style.exclusionBlock">
			<div>
				{{data.label}}
			</div>

			<footer :class="$style.exclusionBlockFooter" v-if="!disabled">
				<div
					:class="$style.dragHandle"
					title="Move this block"
				></div>

				<div>
					<button
						type="button"
						title="Duplicate this block"
					>
						Duplicate
					</button>

					<button
						type="button"
						:class="$style.danger"
						title="Delete this block"
					>
						Delete
					</button>
				</div>
			</footer>
		</div>
	</div>
</template>

<script>
	export default  {
		name: "ExclusionBlock",
		props: ["data", "disabled"],
	}
</script>

<style module lang="less">
	@import "../variables";

	.exclusionBlockWrap {
		padding-bottom: 10px;

		&.disabled {
			opacity: 0.5;
			pointer-events: none;
		}
	}

	.exclusionBlock {
		background: #fff;
		box-shadow: 0 2px 8px 0 rgba(0,0,0,0.05);
		border-radius: 3px;

		transform: translate3d(0, 0, 0);
		will-change: true;

		> div {
			padding: 20px;
		}
	}

	// Footer
	// =========================================================================

	.exclusionBlockFooter {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 20px;

		border-top: 1px solid #DADFEA;

		font-size: 0;
		line-height: 0;

		button {
			margin: 0;
			padding: 0 5px;
			vertical-align: middle;

			color: #8C97B2;
			font-size: 11px;
			font-weight: bold;
			text-align: center;
			text-transform: uppercase;

			appearance: none;
			background: none;
			border: none;
			border-radius: 0;
			cursor: pointer;

			transition: opacity 0.15s ease;

			&:hover {
				opacity: 0.5;
			}

			&:last-child {
				margin-right: -5px;
			}

			&.danger {
				color: @craft-primary;
			}
		}
	}

	.dragHandle {
		display: inline-block;
		width: 23px;
		height: 10px;

		background-image: linear-gradient(
			to bottom,
			#8C97B2 0px,
			#8C97B2 2px,
			transparent 2px,
			transparent 4px,
			#8C97B2 4px,
			#8C97B2 6px,
			transparent 6px,
			transparent 8px,
			#8C97B2 8px,
			#8C97B2 10px
		);

		cursor: move;
	}
</style>