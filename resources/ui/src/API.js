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

	const id = state.id;

	window.Craft.postActionRequest("bookings/api/get-calendar", { body, id }, res => {
		const slots = formatSlotsForStorage(res.slots)
			, exceptions = formatSlotsForStorage(res.exceptions)
			, availability = res.availability
			, hasAnyBookings = res.hasAnyBookings;

		// FIXME: https://github.com/matthewmueller/socrates/issues/21
		store("set", { slots: null, exceptions: null, availability: null });
		store("set", { slots, exceptions, availability, hasAnyBookings });

		// store([
		// 	{ type: "set", payload: { slots: null, exceptions: null } },
		// 	{ type: "set", payload: { slots, exceptions } },
		// ]);
	});
}

// Helpers
// =============================================================================

function formatSlotsForStorage (slots) {
	return slots.reduce((slots, slot) => {
		const d = new Date(slot.date.replace(" ", "T") + "Z");
		d.setUTCSeconds(0);
		d.setUTCMilliseconds(0);

		const fSlot = {};

		fSlot.date = d;
		fSlot.day = fSlot.date.getUTCDay();
		fSlot.hour = fSlot.date.getUTCHours();
		fSlot.minute = fSlot.date.getUTCMinutes();

		const year = fSlot.date.getUTCFullYear()
			, month = fSlot.date.getUTCMonth() + 1
			, date = fSlot.date.getUTCDate()
			, key = fSlot.date.getTime();

		if (!slots.hasOwnProperty(year))
			slots[year] = {};

		if (!slots[year].hasOwnProperty(month))
			slots[year][month] = { all: {} };

		if (!slots[year][month].hasOwnProperty(date))
			slots[year][month][date] = [];

		slots[year][month].all[key] = fSlot;
		slots[year][month][date].push(key);

		return slots;
	}, {});
}