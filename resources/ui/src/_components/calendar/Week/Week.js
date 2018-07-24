// @flow

import { Component } from "preact";
import styles from "./Week.less";
import connect from "../../../_hoc/connect";
import getFirstSlot from "../../../_helpers/getFirstSlot";
import getNearestWeek from "../../../_helpers/getNearestWeek";
import correctDate from "../../../_helpers/correctDate";
import Frequency from "../../../_enums/Frequency";

const DAYS = [
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday",
	"Sunday",
];

const FULL_DAY = 60 * 24;

class Week extends Component {

	// Properties
	// =========================================================================

	state = {
		weeks: [],

		formattedSlots: {},
		formattedExceptions: {},
	};

	// Preact
	// =========================================================================

	componentWillReceiveProps (nextProps) {
		const weeks = Week._computeWeeks(nextProps)
			, formattedSlots = this._formatSlots(nextProps.slots)
			, formattedExceptions = this._formatSlots(nextProps.exceptions);

		this.setState({
			weeks,
			formattedSlots,
			formattedExceptions,
		});
	}

	// Render
	// =========================================================================

	render () {
		return (
			<div class={styles.scroller}>
				{this.state.weeks.map((week, index) => (
					<div key={index} class={styles.group}>
						{this._renderHeader(week)}
						{this._renderLabels()}
						{this._renderCells(week)}
					</div>
				))}
			</div>
		);
	}

	_renderHeader (week) {
		//
	}

	_renderLabels () {
		//
	}

	_renderCells (week) {
		//
	}

	// Helpers
	// =========================================================================

	/**
	 * Computes the number of weeks to show
	 *
	 * @param props
	 * @return {*[]}
	 * @private
	 */
	static _computeWeeks (props) {
		const startDate = getFirstSlot(
			props.slots,
			{ date: new Date() }
		).date;

		const firstWeek = getNearestWeek(
			startDate.getFullYear(),
			startDate.getMonth() + 1,
			startDate.getDate()
		);

		const weeks = [firstWeek];

		// TODO: i should:
		// - Until: Encompass the until date
		// - # Times: The end date of the final slot
		// - Forever: Set to 100
		// - Should be capped at 100.
		let i = 3,
			prevWeek = firstWeek;

		while (--i) {
			const [year, month, day] = correctDate(
				prevWeek.year,
				prevWeek.month,
				prevWeek.day + 7
			);

			const week = { year, month, day };

			weeks.push(weeks);
			prevWeek = week;
		}

		return weeks;
	}

	/**
	 * Formats the given slots
	 *
	 * @param slots
	 * @return {{}}
	 * @private
	 */
	_formatSlots (slots) {
		slots = {...slots};

		for (let y in slots) {
			if (!slots.hasOwnProperty(y))
				continue;

			for (let m in slots[y]) {
				if (!slots[y].hasOwnProperty(m))
					continue;

				for (let key in slots[y][m].all) {
					if (!slots[y][m].all.hasOwnProperty(key))
						continue;

					slots = this._formatSlot(y, m, key, slots);
				}
			}
		}

		return slots;
	}

	/**
	 * Formats the given slot
	 *
	 * @param y
	 * @param m
	 * @param key
	 * @param slots
	 * @return {*}
	 * @private
	 */
	_formatSlot (y, m, key, slots) {
		const { baseRule } = this.props;

		const slot = slots[y][m].all[key];

		// Convert the frequency into minutes
		const numericFreq = baseRule.frequency === Frequency.Minutely ? 1 : 60;

		const fullHeight = numericFreq * baseRule.duration;
		const top = (60 * slot.hour) + slot.minute;
		const heightInclStartOffset = fullHeight + top;

		// If it won't overflow, set position & continue
		if (heightInclStartOffset <= FULL_DAY) {
			slots[y][m].all[key] = {
				...slot,
				position: this._getPosition(
					slot.day,
					slot.hour,
					slot.minute
				),
			};

			return slots;
		}

		// 1. Set the position of the original chunk
		slots[y][m].all[key] = {
			...slot,
			position: this._getPosition(
				slot.day,
				slot.hour,
				slot.minute,
				baseRule.duration + ((FULL_DAY - heightInclStartOffset) / numericFreq)
			),
			splitBottom: true,
		};

		// 2. Add additional chunks

		// Calculate how many extra days & minutes (chunks) this slot overflows by
		const extraPartialChunkHeight = heightInclStartOffset % FULL_DAY;
		let extraWholeChunks = Math.floor(heightInclStartOffset / FULL_DAY),
			previousDate = slot.date.getDate();

		while (extraWholeChunks--) {
			const [ny, nm, nd] = correctDate(y, m, previousDate + 1);
			const nDate = new Date(ny, nm - 1, nd, 0, 0, 0, 0);
			const nKey = nDate.getTime();

			if (!slots.hasOwnProperty(ny))
				slots[ny] = {};

			if (!slots[ny].hasOwnProperty(nm))
				slots[ny][nm] = { all: {} };

			if (!slots[ny][nm].hasOwnProperty(nd))
				slots[ny][nm][nd] = [];

			const isPartial = extraWholeChunks === 0 && extraPartialChunkHeight > 0;

			slots[ny][nm].all[nKey] = {
				...slot,
				position: this._getPosition(
					nDate.getDay(),
					0,
					0,
					isPartial ? extraPartialChunkHeight / numericFreq : 24
				),
				splitTop: true,
				splitBottom: !isPartial,
			};

			if (slots[ny][nm][nd].indexOf(nKey) === -1)
				slots[ny][nm][nd].push(nKey);

			previousDate = nd;
		}

		return slots;
	}

	/**
	 * Returns the X/Y position & height of the given slot occurrence
	 *
	 * @param day
	 * @param hour
	 * @param minute
	 * @param duration
	 * @return {{left: string, top: string, height: string}}
	 * @private
	 */
	_getPosition (day, hour, minute, duration = this.props.baseRule.duration) {
		let d = day === 0 ? 7 : day,
			h = this.props.baseRule.frequency === Frequency.Minutely ? 1 : 60;

		h *= duration;

		return {
			left: 14.285714 * (d - 1) + "%",
			top: (60 * hour) + minute + "px",
			height: h + 1 + "px",
		};
	}

}

export default connect(({ settings: { baseRule }, slots, exceptions }) => ({
	baseRule,
	slots,
	exceptions,
}))(Week);