<template>
	<div :class="[$style.exclusionBlockWrap, { [$style.disabled]: disabled }]">
		<div :class="$style.exclusionBlock">
			<div>
				<Row>
					<!-- Frequency -->
					<Label label="Frequency">
						<Select
							name="frequency"
							v-model="frequency"
							:disabled="disabled"
						>
							<option
								v-for="freq in frequencies"
								:key="freq.value"
								:value="freq.value"
							>
								{{freq.key}}
							</option>
						</Select>
					</Label>

					<!-- Start Date -->
					<Label label="Start Date / Time" elem="div" shrink>
						<DateTime
							name="start"
							v-model="start"
							:disabled="disabled"
						/>
					</Label>
				</Row>

				<Row>
					<!-- Repeats -->
					<Label label="Repeats">
						<Select
							name="repeats"
							v-model="repeats"
							:disabled="disabled"
						>
							<option value="until">Until</option>
							<option value="count"># Times</option>
							<option value="forever">Forever</option>
						</Select>
					</Label>

					<!-- Until Date -->
					<Label
						label="End Date / Time"
						v-if="repeats === 'until'"
						elem="div"
						shrink
					>
						<DateTime
							name="until"
							v-model="until"
							:disabled="disabled"
						/>
					</Label>

					<!-- Count -->
					<Label label="Count" v-if="repeats === 'count'">
						<Input
							type="number"
							:min="1"
							:required="true"
							:disabled="disabled"
							v-model="count"
						/>
					</Label>
				</Row>

				<Row>
					<!-- Interval -->
					<Label label="Interval">
						<Input
							type="number"
							:min="1"
							:required="true"
							:disabled="disabled"
							v-model="interval"
						/>
					</Label>

					<!-- Duration -->
					<Label label="Duration" v-if="includeDuration">
						<Input
							type="number"
							required
							:min="1"
							:disabled="disabled"
							v-model="duration"
						/>
					</Label>

					<!-- Bookable -->
					<Label label="Bookable" v-if="isException">
						<Lightswitch
							v-model="bookable"
						/>
					</Label>
				</Row>
			</div>

			<footer
				:class="$style.exclusionBlockFooter"
				v-if="!disabled && !hideFooter"
			>
				<!-- Move Handle -->
				<div
					:class="[$style.dragHandle, 'bookings--drag-handle']"
					title="Move this block"
					v-if="!noDrag"
				></div>
				<!-- To maintain the flex layout -->
				<span v-if="noDrag"></span>

				<div>
					<!-- Duplicate -->
					<button
						type="button"
						title="Duplicate this block"
						@click="onDuplicateClick"
					>
						Duplicate
					</button>

					<!-- Delete -->
					<button
						type="button"
						:class="$style.danger"
						title="Delete this block"
						@click="onDelete"
					>
						Delete
					</button>
				</div>
			</footer>
		</div>
	</div>
</template>

<script>
	import Row from "./form/Row";
	import Label from "./form/Label";
	import Select from "./form/Select";
	import Input from "./form/Input";
	import Lightswitch from "./form/Lightswitch";
	import DateTime from "./form/DateTime";
	import RecursionRule from "../models/RecursionRule";
	import Frequency from "../const/Frequency";
	import ExRule from "../models/ExRule";

	export default  {
		name: "RRuleBlock",
		props: {
			rrule: RecursionRule,
			id: String,

			hideFooter: {
				type: Boolean,
				default: false,
			},
			includeDuration: {
				type: Boolean,
				default: false,
			},

			disabled: Boolean,
			noDrag: Boolean,
		},
		components: { Row, Label, Select, Input, Lightswitch, DateTime },

		data () {
			let r;

			if (this.rrule)
				r = this.rrule;

			if (this.id)
				r = this.$store.getters.getExceptionById(this.id);

			if (!r)
				throw new Error("Missing RRule or ID");

			return {
				internal_rrule: r,
				...r.convertToDataObject(),
			};
		},

		computed: {
			frequencies: () => Frequency.asKeyValueArray(),

			isException () {
				return this.internal_rrule.constructor === ExRule;
			}
		},

		mounted () {
			this.$watch("$data", this.onUpdateRule, { deep: true });
		},

		methods: {

			/**
			 * Updates the current rule in Vuex
			 *
			 * @param {Object} next
			 */
			onUpdateRule (next) {
				const rule =
					this.isException
						? new ExRule(next)
						: new RecursionRule(next);

				rule.id = this.id;

				this.$store.dispatch(
					"updateRule",
					rule
				);
			},

			/**
			 * Duplicates the current exception
			 */
			onDuplicateClick () {
				this.$store.dispatch("duplicateExceptionById", this.id);
			},

			/**
			 * Deletes the current exception
			 */
			onDelete () {
				// TODO: Better confirmation

				if (confirm("Are you sure?"))
					this.$store.dispatch("deleteExceptionById", this.id);
			}
		}
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

		border-top: 1px solid @border;

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