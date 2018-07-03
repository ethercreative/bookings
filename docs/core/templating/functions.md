---
title: Functions
---

# Functions

Bookings provides a couple of template functions to make generating the booking
forms easier:

## `placeBookingInput(BookableField)`

This will generate the secure hidden fields that will allow you to reserve a 
booking for the given Bookable. See [Reserving a slot](../example-templates/reserve-slot.md) for a complete example.

```twig
{{ placeBookingInput(entry.myBookableField) }}
```

## `confirmBookingInput(Booking)`

This will generate the secure hidden fields that will allow you to confirm the 
given booking. See [Confirming a booking](../example-templates/confirm-booking.md) for a complete example.

```twig
{% set booking = craft.bookings.email('customer@email.com').one() %}
{{ confirmBookingInput(booking) }}
```