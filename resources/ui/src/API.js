import store from "./store";
import modelToRRule from "./_helpers/modelToRRule";

// API
// =============================================================================

export function refreshCalendar (state = store()) {
	const settings = state.settings;

	const body = JSON.stringify({
		baseRule: modelToRRule(settings.baseRule),
		exceptions: Object.values(settings.exceptions).map(r => modelToRRule(r)),
	});

	window.Craft.postActionRequest("bookings/api/get-calendar", { body }, res => {
		const slots = formatSlotsForStorage(res.slots)
			, exceptions = formatSlotsForStorage(res.exceptions);

		// FIXME: https://github.com/matthewmueller/socrates/issues/21
		store("set:slots", slots);
		store("set:exceptions", exceptions);

		// store([
		// 	{ type: "set:slots", payload: slots },
		// 	{ type: "set:exceptions", payload: exceptions },
		// ]);
	});
}

// Helpers
// =============================================================================

function formatSlotsForStorage (slots) {
	return slots.reduce((slots, slot) => {
		const d = new Date(slot.date);
		d.setSeconds(0);
		d.setMilliseconds(0);

		slot.date = d;
		slot.day = slot.date.getDay();
		slot.hour = slot.date.getHours();
		slot.minute = slot.date.getMinutes();

		const year = slot.date.getFullYear()
			, month = slot.date.getMonth() + 1
			, date = slot.date.getDate()
			, key = slot.date.getTime();

		if (!slots.hasOwnProperty(year))
			slots[year] = {};

		if (!slots[year].hasOwnProperty(month))
			slots[year][month] = { all: {} };

		if (!slots[year][month].hasOwnProperty(date))
			slots[year][month][date] = [];

		slots[year][month].all[key] = slot;
		slots[year][month][date].push(key);

		return slots;
	}, {});
}