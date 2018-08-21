---
title: Availability
sidebarDepth: 3
---

# Availability

[[toc]]

## How to query

You can query availability from your templates using `craft.availability`.
While some of the syntax is similar this **does not** return an Element Query.

::: code

```twig
craft.availability(event).all()
```

```php
(new \ether\bookings\common\Availability(Event $event))->all();
```

:::

## Parameters

### `ticket(Ticket|null)`

Check availability for the given ticket of the event.

::: code

```twig
{% set query = craft.availability(entry.eventField).ticket(entry.variants.one().ticketField) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myEvent))->ticket($myTicket);
```

:::

### `start(DateTime|string|null)`

Query availability from the given date. Must either be or be parse-able by [DateTime](http://php.net/manual/en/class.datetime.php).

::: code

```twig
{% set query = craft.availability(entry.eventField).start(now|date_modify('-1 week')) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myEvent))->start('2018-07-01');
```

:::

### `end(DateTime|string|null)`

Query availability until the given date. Must either be or be parse-able by [DateTime](http://php.net/manual/en/class.datetime.php).

::: code

```twig
{% set query = craft.availability(entry.eventField).end(now|date_modify('+1 week')) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myEvent))->end('2018-07-10');
```

:::

### `limit(int|null)`

Limit the number of slots by the given amount. Will be ignored if `end` is set.

::: code

```twig
{% set query = craft.availability(entry.eventField).limit(10) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myEvent))->limit(10);
```

:::

### `groupBy(string|null)`

Group the results by a given frequency.

Allowed Frequencies:
- `hour`
- `day`
- `week`
- `month`
- `year`

::: code

```twig
{% set query = craft.availability(entry.eventField).groupBy('day') %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myEvent))->groupBy('day');
```

:::