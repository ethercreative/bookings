---
title: Stand Alone
---

# Stand Alone

### Reserve a slot

```twig
<form method="post">
	{{ csrfInput() }}
	<input type="hidden" name="action" value="bookings/book">
	{{ entry.bookable.input() }}

	{% if booking is defined and booking.hasErrors() %}
		<div>
			<pre><code>{{ dump(booking.getErrors()) }}</code></pre>
		</div>
	{% endif %}

	<input type="email" name="customerEmail" placeholder="Email" value="{{ booking is defined ? booking.customerEmail }}">

	<br>
	<input type="date" placeholder="Start Date" id="startDate" value="{{ booking is defined ? booking.slotStart|date('Y-m-d') }}">
	<input type="time" placeholder="Start Time" id="startTime" value="{{ booking is defined ? booking.slotStart|date('g:i') }}">
	<input type="hidden" name="slotStart" id="slotStart" value="{{ booking is defined ? booking.slotStart|date('Y-m-d g:i') }}">

	{% if entry.bookable.bookableType == 'flexible' %}
		<br>
		<input type="date" placeholder="End Date" id="endDate" value="{{ booking is defined ? booking.slotEnd|date('Y-m-d') }}">
		<input type="time" placeholder="End Time" id="endTime" value="{{ booking is defined ? booking.slotEnd|date('g:i') }}">
		<input type="hidden" name="slotEnd" id="slotEnd" value="{{ booking is defined ? booking.slotEnd|date('Y-m-d g:i') }}">
	{% endif %}

	<br>
	<button>Book</button>
</form>
```

### Confirm a booking

```twig
{% for booking in craft.bookings.email('tam@ethercreative.co.uk').all() %}
    {% if not booking.isCompleted and not booking.expired %}
        <form method="post">
            {{ csrfInput() }}
            {{ confirmBookingInput(booking) }}
            <input type="hidden" name="action" value="bookings/book/confirm">
            <button>Confirm</button>
        </form>
    {% endif %}
{% endfor %}
```