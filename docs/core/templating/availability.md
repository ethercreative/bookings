---
title: Querying Availability
sidebarDepth: 3
---

# Querying Availability

[[toc]]

## How to query

You can query availability from your templates using `craft.availability`.
Unlike [Bookings](./bookings.md), and while some of the syntax is similar, 
this **does not** return an Element Query.

::: code

```twig
craft.availability(bookable)
```

```php
new \ether\bookings\common\Availability(Bookable $bookable);
```

:::

## Parameters

### `start(DateTime|string|null)`

Query availability from the given date. Must either be or be parse-able by [DateTime](http://php.net/manual/en/class.datetime.php).

::: code

```twig
{% set query = craft.availability(entry.bookableField).start(now|date_modify('-1 week')) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myBookable))->start('2018-07-01');
```

:::

### `end(DateTime|string|null)`

Query availability until the given date. Must either be or be parse-able by [DateTime](http://php.net/manual/en/class.datetime.php).

::: code

```twig
{% set query = craft.availability(entry.bookableField).end(now|date_modify('+1 week')) %}
```

```php
use \ether\bookings\common\Availability;

$availability = (new Availability($myBookable))->end('2018-07-10');
```

:::