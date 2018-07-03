---
title: Confirm a Booking
---

# Confirm a Booking

```twig
{% for booking in craft.bookings.email('customers@email.com').all() %}
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