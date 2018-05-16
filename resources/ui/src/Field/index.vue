<!--suppress JSXNamespaceValidation -->
<script>
	import { mapState } from "vuex";
	import ExceptionsModal from "./ExceptionsModal";
	import RRuleBlock from "../components/RRuleBlock";
	import CraftFieldHeading from "../components/CraftFieldHeading";
	import Button from "../components/form/Button";
	import BookableType from "../enums/BookableType";

	export default {
		name: "field",

		components: { ExceptionsModal, RRuleBlock, Button, CraftFieldHeading },

		data () {
			return {
				exceptionsModalOpen: false,
				internal_bookableType: this.bookableType,
			};
		},

		computed: {
			...mapState([
				"baseRule",
				"exceptions",
				"exceptionsSort",
				"slotDuration",
				"bookableType",
			]),
		},

		beforeCreate () {
			this.$store.dispatch(
				"setDefaultState",
				this.$parent.$data.options.value
			);
		},

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
							"The user can book a single slot at a time.\r\nConvenient for concerts, cookery classes, etc.",
							(
								<svg width="50px" height="50px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg">
									<g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
										<g transform="translate(-423.000000, -319.000000)">
											<g transform="translate(423.000000, 319.000000)">
												<circle fill="#5186D9" cx="25" cy="25" r="25" />
												<g transform="translate(13.000000, 13.000000)" fill="#FFFFFF" fillRule="nonzero">
													<path d="M22,4 L2,4 L2,22 L22,22 L22,4 Z M24,22 C24,23.1022847 23.1022847,24 22,24 L2,24 C0.89771525,24 0,23.1022847 0,22 L0,4 C0,2.89771525 0.89771525,2 2,2 L22,2 C23.1022847,2 24,2.89771525 24,4 L24,22 Z" />
													<path d="M7,1 C7,0.44771525 7.44771525,0 8,0 C8.55228475,0 9,0.44771525 9,1 L9,5 C9,5.55228475 8.55228475,6 8,6 C7.44771525,6 7,5.55228475 7,5 L7,1 Z M15,1 C15,0.44771525 15.4477153,0 16,0 C16.5522847,0 17,0.44771525 17,1 L17,5 C17,5.55228475 16.5522847,6 16,6 C15.4477153,6 15,5.55228475 15,5 L15,1 Z M17,12 C16.4477153,12 16,11.5522847 16,11 L16,9 C16,8.44771525 16.4477153,8 17,8 L19,8 C19.5522847,8 20,8.44771525 20,9 L20,11 C20,11.5522847 19.5522847,12 19,12 L17,12 Z M17,18 C16.4477153,18 16,17.5522847 16,17 L16,15 C16,14.4477153 16.4477153,14 17,14 L19,14 C19.5522847,14 20,14.4477153 20,15 L20,17 C20,17.5522847 19.5522847,18 19,18 L17,18 Z M5,12 C4.44771525,12 4,11.5522847 4,11 L4,9 C4,8.44771525 4.44771525,8 5,8 L7,8 C7.55228475,8 8,8.44771525 8,9 L8,11 C8,11.5522847 7.55228475,12 7,12 L5,12 Z M5,18 C4.44771525,18 4,17.5522847 4,17 L4,15 C4,14.4477153 4.44771525,14 5,14 L7,14 C7.55228475,14 8,14.4477153 8,15 L8,17 C8,17.5522847 7.55228475,18 7,18 L5,18 Z M11,12 C10.4477153,12 10,11.5522847 10,11 L10,9 C10,8.44771525 10.4477153,8 11,8 L13,8 C13.5522847,8 14,8.44771525 14,9 L14,11 C14,11.5522847 13.5522847,12 13,12 L11,12 Z M11,18 C10.4477153,18 10,17.5522847 10,17 L10,15 C10,14.4477153 10.4477153,14 11,14 L13,14 C13.5522847,14 14,14.4477153 14,15 L14,17 C14,17.5522847 13.5522847,18 13,18 L11,18 Z" />
												</g>
											</g>
										</g>
									</g>
								</svg>
							)
						)}
						{this._renderTypeButton(
							BookableType.FLEXIBLE,
							"Flexible Slots",
							"The user can book a range of slots.\r\nHandy for hotels, hardware hire, etc.",
							(
								<svg width="50px" height="50px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg">
									<g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
										<g transform="translate(-794.000000, -319.000000)">
											<g transform="translate(647.000000, 299.000000)">
												<g transform="translate(147.000000, 20.000000)">
													<circle fill="#9C9C9C" cx="25" cy="25" r="25" />
													<g transform="translate(13.000000, 13.000000)" fill="#FFFFFF" fillRule="nonzero">
														<path d="M22,4 L2,4 L2,22 L22,22 L22,4 Z M24,22 C24,23.1022847 23.1022847,24 22,24 L2,24 C0.89771525,24 0,23.1022847 0,22 L0,4 C0,2.89771525 0.89771525,2 2,2 L22,2 C23.1022847,2 24,2.89771525 24,4 L24,22 Z" />
														<path d="M6,1 C6,0.44771525 6.44771525,0 7,0 C7.55228475,0 8,0.44771525 8,1 L8,5 C8,5.55228475 7.55228475,6 7,6 C6.44771525,6 6,5.55228475 6,5 L6,1 Z M16,1 C16,0.44771525 16.4477153,0 17,0 C17.5522847,0 18,0.44771525 18,1 L18,5 C18,5.55228475 17.5522847,6 17,6 C16.4477153,6 16,5.55228475 16,5 L16,1 Z M15.2415024,10.3483242 C15.601413,9.9294175 16.2327692,9.88159178 16.6516758,10.2415024 C17.0705825,10.601413 17.1184082,11.2327692 16.7584976,11.6516758 L11.6034976,17.6516758 C11.2166238,18.1019655 10.5249324,18.118029 10.1175723,17.6861843 L7.27257228,14.6701843 C6.89360315,14.2684371 6.91206845,13.6355414 7.31381569,13.2565723 C7.71556293,12.8776031 8.34845859,12.8960685 8.72742772,13.2978157 L10.8103013,15.5058813 L15.2415024,10.3483242 Z" />
													</g>
												</g>
											</g>
										</g>
									</g>
								</svg>
							)
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

					<input
						type="hidden"
						name={this.$parent.$data.options.handle}
						value={JSON.stringify({
							baseRule: this.baseRule,
							exceptions: this.exceptionsSort.map(id => this.exceptions[id]),
							slotDuration: this.slotDuration,
							bookableType: this.bookableType,
						})}
					/>
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
						onClick={() => { this.onBookableTypeClick(type); }}
					>
						{icon}
						<strong>{name}</strong>
						<span>{instructions}</span>
					</button>
				);
			},

			// Events
			// -----------------------------------------------------------------

			onBookableTypeClick (type) {
				this.internal_bookableType = type;
				this.$store.dispatch(
					"updateBookableType",
					type
				);
			},

		},
	}
</script>

<style module lang="less">
	@import "../variables";

	.wrap {
		position: relative;
		padding-top: @spacer;

		&:before {
			content: '';
			position: absolute;
			top: 0;
			left: -@spacer;
			right: -@spacer;

			display: block;
			height: 1px;
			background-color: #E3E5E8;
		}

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

		svg {
			margin-bottom: 10px;
		}

		circle {
			fill: #9C9C9C;
			transition: fill 0.15s ease;
		}

		strong {
			margin-bottom: 5px;

			font-size: 16px;
			font-weight: 500;
			letter-spacing: 0;
		}

		span {
			color: #9C9C9C;
			font-size: 12px;
			letter-spacing: 0;
			text-align: center;
			line-height: 18px;
			white-space: pre;
		}

		&:focus,
		&:hover {
			opacity: 1;
		}

		&.active {
			border: 1px solid #5186D9;
			box-shadow: 0 2px 9px 0 rgba(81,134,217,0.30);

			opacity: 1;

			circle {
				fill: #5186D9;
			}
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