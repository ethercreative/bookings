import { Component } from "preact";
import styles from "./Field.less";
import connect from "../_hoc/connect";
import CraftField from "../_components/CraftField";
import CraftLightSwitch from "../_components/CraftLightSwitch";
import BookableType from "../_enums/BookableType";
import TypeButton from "../_components/TypeButton/TypeButton";
import CraftButton from "../_components/CraftButton";
import FixedIcon from "../_icons/FixedIcon";
import FlexibleIcon from "../_icons/FlexibleIcon";
import CalendarIcon from "../_icons/CalendarIcon";

class Field extends Component {

	// Events
	// =========================================================================

	onTypeButtonClick = (e, type) => {
		e.preventDefault();
		this.props.dispatch("set:settings.bookableType", type);
	};

	// Render
	// =========================================================================

	render ({ dispatch, handle, enabled, bookableType }) {
		let cls = [styles.field];
		if (!enabled) cls.push(styles.disabled);
		cls = cls.join(" ");

		return (
			<div className={styles.wrap}>
				<CraftField
					label="Enable Bookings"
					instructions="Allow users to book this element."
				>
					<CraftLightSwitch
						name={`${handle}[enabled]`}
						on={enabled}
						onChange={checked => dispatch("set:enabled", checked)}
					/>
				</CraftField>

				<CraftField
					label="Bookable Type"
					instructions="Select the type of bookable event this is."
					className={cls}
				>
					<div className={styles.types}>
						<TypeButton
							type={BookableType.FIXED}
							active={bookableType === BookableType.FIXED}
							name="Fixed Slots"
							instructions={"The user can book a single slot at a time.\r\nConvenient for concerts, cookery classes, etc."}
							icon={FixedIcon}
							onClick={this.onTypeButtonClick}
						/>

						<TypeButton
							type={BookableType.FLEXIBLE}
							active={bookableType === BookableType.FLEXIBLE}
							name="Flexible Slots"
							instructions={"The user can book a range of slots.\r\nHandy for hotels, hardware hire, etc."}
							icon={FlexibleIcon}
							onClick={this.onTypeButtonClick}
							disabled
						/>
					</div>
				</CraftField>

				<CraftField
					label="Bookable Rules"
					instructions="Add rules to either add bookable space, or remove it from the primary booking window"
					className={cls}
				>
					<div className={styles.well}>
						<header>
							<h5>Primary Rule</h5>

							<CraftButton type="button" className="submit">
								{CalendarIcon}
								Edit Rules
							</CraftButton>
						</header>
					</div>
				</CraftField>
			</div>
		);
	}

}

export default connect(({ handle, enabled, settings: { bookableType } }) => ({
	handle,
	enabled,
	bookableType,
}))(Field);