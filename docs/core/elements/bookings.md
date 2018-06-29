---
title: Querying Bookings
sidebarDepth: 3
---

# Querying Bookings

## How to query

You can access your site's bookings from your templates using `craft.bookings`.
This returns an [Element Query](https://docs.craftcms.com/v3/element-queries.html#creating-element-queries).

### PHP

```php
\ether\bookings\elements\Booking::find()
```

### Twig

```twig
craft.bookings()
```

## Parameters

### `completed(bool)`

Find bookings that are or are not marked as completed.

```twig
{% set query = craft.bookings.completed(true) %}
```