/** global: Craft, Garnish */

Craft.Bookings = Craft.Bookings || {};

Craft.Bookings.BookingIndex = Craft.BaseElementIndex.extend({

	// Properties
	// =========================================================================

	status: 1,

});

Craft.registerElementIndexClass(
	"ether\\bookings\\elements\\Booking",
	Craft.Bookings.BookingIndex
);