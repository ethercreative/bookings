<!--suppress JSXNamespaceValidation -->
<script>
	import ExceptionsModal from "./ExceptionsModal";
	import RRuleBlock from "../components/RRuleBlock";
	import CraftFieldHeading from "../components/CraftFieldHeading";
	import Button from "../components/form/Button";
	import RecursionRule from "../models/RecursionRule";
	import BookableType from "../enums/BookableType";

	export default {
		name: "field",

		components: { ExceptionsModal, RRuleBlock, Button, CraftFieldHeading },

		data: () => ({
			BookableType,

			exceptionsModalOpen: false,
			baseRule: new RecursionRule(),
			bookableType: BookableType.FIXED,
		}),

		// Render
		// =====================================================================

		render () {
			return (
				<div class={this.$style.wrap}>
					<CraftFieldHeading
						label="Bookable Type"
						instructions="Select the type of bookable event this is."
					/>

					<div class={this.$style.types}>
						{this._renderTypeButton(
							BookableType.FIXED,
							"Fixed Slots",
							"The user can book a single slot at a time. Convenient for concerts, cookery classes, etc.",
							null
						)}
						{this._renderTypeButton(
							BookableType.FLEXIBLE,
							"Flexible Slots",
							"The user can book a range of slots. Handy for hotels, hardware hire, etc.",
							null
						)}
					</div>

					<CraftFieldHeading
						label="Bookable Rules"
						instructions="Add rules to either add bookable space, or remove it from the primary booking window"
					/>

					<div class={this.$style.well}>
						<header>
							<h5>Primary Rule</h5>

							<Button onClick={() => { this.exceptionsModalOpen = true; }}>
								<svg width="14px" height="15px" viewBox="0 0 14 15" version="1.1" xmlns="http://www.w3.org/2000/svg">
									<g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
										<g transform="translate(-875.000000, -613.000000)" fill="#FFFFFF" fillRule="nonzero">
											<g transform="translate(875.000000, 613.000000)">
												<path d="M0,2 L2,2 L2,1 C2,0.44771525 2.44771525,1.01453063e-16 3,0 C3.55228475,-1.01453063e-16 4,0.44771525 4,1 L4,2 L10,2 L10,1 C10,0.44771525 10.4477153,1.01453063e-16 11,0 C11.5522847,-1.01453063e-16 12,0.44771525 12,1 L12,2 L14,2 L14,5 L0,5 M0,6 L14,6 L14,15 L0,15" />
											</g>
										</g>
									</g>
								</svg>
								Edit Rules
							</Button>
						</header>

						<RRuleBlock
							rrule={this.baseRule}
							hideFooter
							includeDuration
						/>
					</div>

					<ExceptionsModal
						open={this.exceptionsModalOpen}
						close={() => { this.exceptionsModalOpen = false; }}
					/>

					<portal-target name="modals" multiple />
				</div>
			);
		},

		// Methods
		// =====================================================================

		methods: {

			// Render
			// -----------------------------------------------------------------

			_renderTypeButton (type, name, instructions, icon) {
				const cls = [this.$style.type];
				if (type === this.bookableType)
					cls.push(this.$style.active);

				return (
					<button
						type="button"
						class={cls.join(" ")}
						onClick={() => { this.bookableType = type; }}
					>
						{icon}
						<strong>{name}</strong>
						<span>{instructions}</span>
					</button>
				);
			},

		},
	}
</script>

<style module lang="less">
	@import "../variables";

	.wrap {
		&,
		& * {
			box-sizing: border-box;
		}
	}

	.types {
		display: flex;
		align-items: stretch;
		justify-content: space-between;
		margin-bottom: @spacer*2;
	}

	.type {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		width: calc(~"50% - " @spacer/2);

		padding: @spacer;

		appearance: none;
		background: #FFFFFF;
		border: 1px solid #E3E5E8;
		box-shadow: 0 2px 6px 0 rgba(35,36,46,0.08);
		border-radius: 2px;
		cursor: pointer;
		outline: none;

		opacity: 0.5;

		transition: opacity 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;

		strong {
			margin-bottom: 5px;

			font-size: 16px;
			font-weight: 600;
			letter-spacing: 0;
		}

		span {
			color: #9C9C9C;
			font-size: 12px;
			letter-spacing: 0;
			text-align: center;
			line-height: 18px;
		}

		&:focus,
		&:hover {
			opacity: 1;
		}

		&.active {
			border: 1px solid #5186D9;
			box-shadow: 0 2px 9px 0 rgba(81,134,217,0.30);

			opacity: 1;
		}
	}

	.well {
		width: calc(~"100% + "@spacer*2);
		margin: @spacer -@spacer 0;
		padding: @spacer;

		background: @aside-bg;
		border: 1px solid #E3E5E8;
		border-left: none;
		border-right: none;

		header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: @spacer/2;
		}

		h5 {
			color: #8C97B2;
			font-size: 15px;
			font-weight: normal;
			letter-spacing: 0;
		}

		svg {
			vertical-align: middle;
			margin: -3px 10px 0 0;
		}
	}
</style>