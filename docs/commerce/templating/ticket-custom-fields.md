---
title: Ticket Custom Fields
sidebarDepth: 3
---

# Ticket Custom Fields

When creating a `Bookable Ticket` field you have the option to build a custom field 
layout. This field layout can be populated by customers when they book, useful
for any additional meta data you may require for your tickets (such as names, 
dietary requirements, etc.).

Each ticket booked will have its own fields for the customer to fill out. For 
example if a customer books two of the same ticket, they will have two sets of 
fields to fill.

## Adding Content

The code below shows how you can update a booked tickets contents (currently 
this is only possible after a booking has been added to a cart, not during).

A `ticket` variable will be returned, which you can use to get any errors on the 
ticket.

::: code

```twig
{% for item in cart.lineItems %}
    {% for ticket in getBookedTickets(item) %}
        <form method="post">
            {{ csrfInput() }}
            <input type="hidden" name="action" value="bookings/save-booked-ticket" />
            <input type="hidden" name="id" value="{{ ticket.id }}" />
            
            <input type="text" name="fields[myField]" value="{{ ticket.myField }}" />
            
            <button>Save</button>
        </form>
    {% endfor %}
{% endfor %}
```

:::

### Updating multiple tickets

A `tickets` array, keyed by the ID of the ticket, will be returned.

::: code

```twig
<form method="post">
    {{ csrfInput() }}
    <input type="hidden" name="action" value="bookings/save-booked-ticket" />
    {% for item in cart.lineItems %}
        {% for ticket in getBookedTickets(item) %}
            <input type="text" name="tickets[{{ ticket.id }}][myField]" value="{{ ticket.myField }}" />
        {% endfor %}
    {% endfor %}  
    <button>Save</button>
</form>
```

:::

## Reading Content

You can access an array of all booked tickets for a given line item using the 
`getBookedTickets` helper function in Twig, or by querying the `BookedTicket` 
element directly in PHP.

::: code

```twig
{% set bookedTickets = getBookedTickets(cart.lineItems[0]) %}

{{ bookedTickets[0].myField }}
```

```php
use ether\bookings\elements\BookedTicket;

$bookedTickets = BookedTicket::findAll([
    'lineItemId' => $lineItem->id,
]);

echo $bookedTickets[0]->myField;
```

:::