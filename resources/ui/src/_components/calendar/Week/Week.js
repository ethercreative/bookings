// @flow

import { Component } from "preact";
import styles from "./Week.less";
import connect from "../../../_hoc/connect";
import getFirstSlot from "../../../_helpers/getFirstSlot";
import getNearestWeek from "../../../_helpers/getNearestWeek";
import correctDate from "../../../_helpers/correctDate";
import Frequency from "../../../_enums/Frequency";
import slotExists from "../../../_helpers/slotExists";
import padZero from "../../../_helpers/padZero";
import getLastSlot from "../../../_helpers/getLastSlot";

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

	componentDidMount () {
		this.updateStateFromProps();
	}

	componentWillReceiveProps (nextProps) {
		this.updateStateFromProps(nextProps);
	}

	// Actions
	// =========================================================================

	updateStateFromProps (props = this.props) {
		const weeks = Week._computeWeeks(props)
			, formattedSlots = this._formatSlots(props.slots)
			, formattedExceptions = this._formatSlots(props.exceptions);

		this.setState({
			weeks,
			formattedSlots,
			formattedExceptions,
		});
	}

	// Render
	// =========================================================================

	render (_, { weeks }) {
		return (
			<div class={styles.scroller}>
				{weeks.map((week, index) => (
					<div key={index} class={styles.group}>
						{Week._renderHeader(week)}
						{Week._renderLabels()}
						{this._renderCells(week)}
					</div>
				))}
			</div>
		);
	}

	static _renderHeader (week) {
		return (
			<header class={styles.header}>
				<div>
					{DAYS.map((day, i) => (
						<span key={i}>
							{Week._getHeader(day, week, i)}
						</span>
					))}
				</div>
			</header>
		);
	}

	static _renderLabels () {
		return (
			<ul class={styles.labels}>
				{Array.from({ length: 23 }, (_, i) => {
					let t = i + 1;

					if (t > 12) t = (t - 12) + " pm";
					else t = t + " am";

					return <li key={i}>{t}</li>;
				})}
			</ul>
		);
	}

	_renderCells (week) {
		const { formattedSlots, formattedExceptions } = this.state;

		return (
			<div class={styles.cells}>
				{DAYS.map((day, i) => {
					const [y, m, d] = Week._correctDayByWeek(week, i);

					let ret = [];

					if (slotExists(formattedSlots, y, m, d))
						ret = ret.concat(this._renderSlot(y, m, d));

					if (slotExists(formattedExceptions, y, m, d))
						ret = ret.concat(this._renderException(y, m, d));

					return ret;
				})}
			</div>
		);
	}

	_renderSlot (y, m, d) {
		const { formattedSlots } = this.state;

		return formattedSlots[y][m][d].map(id => {
			const slot = formattedSlots[y][m].all[id]
				, cls = [styles.slot];

			if (slot.splitTop)
				cls.push(styles["split-top"]);

			if (slot.splitBottom)
				cls.push(styles["split-bottom"]);

			return (
				<span
					key={id}
					style={slot.position}
					class={cls.join(" ")}
				>
					<span>
						Bookable
						<em>{this._getDuration(slot)}</em>
					</span>
				</span>
			);
		});
	}

	_renderException (y, m, d) {
		const { formattedExceptions } = this.state;

		return formattedExceptions[y][m][d].map(id => {
			const slot = formattedExceptions[y][m].all[id];

			return (
				<span
					key={id}
					style={slot.position}
					class={styles.exception}
				/>
			);
		});
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

		const endDate = getLastSlot(
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
		let i = Math.round((endDate - startDate) / (7 * 24 * 60 * 60 * 1000)) + 1,
			prevWeek = firstWeek;

		while (--i) {
			const [year, month, day] = correctDate(
				prevWeek.year,
				prevWeek.month,
				prevWeek.day + 7
			);

			const week = { year, month, day };

			weeks.push(week);
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

		// Un-freeze the slots object (only an issue in dev)
		if (process.env.NODE_ENV === "development")
			slots = JSON.parse(JSON.stringify(slots));

		slots = { ...slots };

		for (let y in slots) {
			if (!slots.hasOwnProperty(y))
				continue;

			for (let m in slots[y]) {
				if (!slots[y].hasOwnProperty(m))
					continue;

				for (let key in slots[y][m].all) {
					if (!slots[y][m].all.hasOwnProperty(key))
						continue;

					// Fix loss of Date obj after thaw (dev only)
					if (process.env.NODE_ENV === "development")
						slots[y][m].all[key].date = new Date(slots[y][m].all[key].date);

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

	static _getHeader (day, week, i) {
		const [, m, d] = Week._correctDayByWeek(week, i);
		return day + ` ${d}/${m}`;
	}

	static _correctDayByWeek (week, i) {
		return correctDate(
			week.year,
			week.month,
			week.day + i
		);
	}

	_getDuration (slot) {
		const { baseRule } = this.props;
		const h = slot.hour === 24 ? 0 : slot.hour;

		const from = padZero(h) + ":" + padZero(slot.minute);
		let to = "";

		if (baseRule.frequency === Frequency.Minutely) {
			let min = slot.minute + baseRule.duration,
				hr  = slot.hour;

			if (min >= 60) {
				min -= 60;
				hr  += 1;
			}

			if (hr >= 24)
				hr -= 24;

			to = padZero(hr) + ":" + padZero(min);
		}

		// Else Hourly
		// (this view shouldn't be visible for other frequencies)
		else {
			let hr = slot.hour + baseRule.duration;

			if (hr >= 24)
				hr -= 24;

			to = padZero(hr) + ":" + padZero(slot.minute);
		}

		return from + " - " + to;
	}

}

export default connect(({ settings: { baseRule }, slots, exceptions }) => ({
	baseRule,
	slots,
	exceptions,
}))(Week);