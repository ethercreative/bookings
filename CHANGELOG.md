## 1.0.0-alpha.20 - 2018-11-30
### Fixed
- Fixed issue with bookings fields being used before bookings has been initialized.

## 1.0.0-alpha.19 - 2018-11-29
### Fixed
- Fixed a bug where booked slots were tripled on order complete.

## 1.0.0-alpha.18 - 2018-11-29
### Fixed
- Fixed bug where more tickets were created than the qty booked

## 1.0.0-alpha.17 - 2018-11-22
### Fixed
- Fixed booked ticket custom field data being when line items were updated

## 1.0.0-alpha.16 - 2018-11-22

> {warning} Previously we were artificially increasing the interval by the 
duration to prevent the overlap of slots. This was causing issues if your slots 
were more disparate, so we have removed this functionality. This **will cause 
issues** with your recurrence rules. To fix these issues simply increase your 
interval by your duration.

### Added
- Added "By Day" field to calendar UI.
- Added support for updating multiple booked tickets custom fields in a single
request.
- Custom booked ticket fields now show (static) in the booking edit screen.

### Changed
- If event multiplier is 0, availability will show the capacity.
- **Breaking:** Interval is no longer artificially increased by the duration.

### Fixed
- Fixed capacity and multiplier being set to 1 after being set to 0.
- Fixed day view showing duration end minutes incorrectly above a certain time [#21]
- Fixed minutes that end on an hour from extending an extra hour [#22]
- Fixed issues with availability checking when using a multiplier of 0.
- Fixed booking expire command timing out.

[#21]: https://github.com/ethercreative/bookings/issues/21
[#22]: https://github.com/ethercreative/bookings/issues/22

## 1.0.0-alpha.15 - 2018-11-14
### Added
- Booked tickets can now have custom fields (via the Bookable Ticket field)!

## 1.0.0-alpha.14 - 2018-11-12
### Added
- Added element sorting parameter `bookings:nextSlot`, see the [Sorting] docs for more.

[Sorting Docs]: http://localhost:8080/bookings/commerce/templating/sorting.html

## 1.0.0-alpha.13 - 2018-10-09
### Added
- It's now possible to update a bookings slot via the admin.

## 1.0.0-alpha.12
### Fixed
- Fixed Query errors in Postgres

### Improved
- The slots of refunded orders are now made available again

## 1.0.0-alpha.11
### Added
- Added `$date` param to `Event::getNextAvailableSlot` to get the next available 
date after the given date.
- New bookings view
- Export bookings to CSV (basic)

### Fixed
- Fixed getNextAvailableSlot shows the current date if booking is not enabled [#8]

[#8]: https://github.com/ethercreative/bookings/issues/8

## 1.0.0-alpha.10 - 2018-09-28
### Added
- Added searchable attributes to bookings

### Fixed
- Fixed SQL error when running expire command
- Fixed JSON column error on MySQL DB's
- Store customer email from order on booking
- Fixed incorrect qty when checking availability
- Fixed unknown column error on the Bookings index when using Postgres

## 1.0.0-alpha.9 - 2018-09-05
### Fixed
- Fixed bug where a booking would make itself unavailable

## 1.0.0-alpha.8 - 2018-09-05
### Fixed
- All dates are converted to UTC. Always.
- Fixed slot dates drifting back by the timezone difference on subsequent saves.

## 1.0.0-alpha.7 - 2018-08-30
### Fixed
- Fixed Booking CP not filtering correctly.
- Fixed Event level slot capacity not taking multiplier into account.
- Fixed availability query

## 1.0.0-alpha.6.2 - 2018-08-23
### Fixed
- Fixed Booking CP section erring when DB was prefixed.

## 1.0.0-alpha.6.1
### Fixed
- Fixed migration filling DB column with incorrectly formatted date

## 1.0.0-alpha.6
### Fixed
- Fixed postgresql throwing error when querying availability
- Fixed Recursion Rule issue when given empty timezone

### Added
- Filter elements by their event fields slots using before / after times

### Improved
- Days / Weeks without bookable slots are skipped in the Calendar UI

## 1.0.0-alpha.5
### Fixed
- Passing start date when checking availability no longer makes that date the primary rules start date.
- Bookings are unique per-slot
- Updating stock in the cart validates correctly

### Improved
- Querying availability in range now much faster

### Added
- Added `Event::getNextAvailableSlot()`

## 1.0.0-alpha.4
### Fixed
- Fixed availability error when database has prefix
- End time correctly taken into account when getting range of slots
- Fixed RRule date normalization
- Fixed checking availability between timezones
- The event field calendars now show if a slot has bookings / is fully booked
- Primary rule editing is disabled if the event has bookings

### Added
- Added `getDurationInMinutes` to `RecursionRule`

## 1.0.0-alpha.3 - 2018-08-10
### Fixed
- Added check for `ticketId` in order options before getting.

## 1.0.0-alpha.1 - 2018-04-03
### Added
- Initial release
