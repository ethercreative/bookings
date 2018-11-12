---
title: Sorting
sidebarDepth: 3
---

# Sorting

[[toc]]

## How to sort

Sorting is the same as a regular Craft [Element Query](https://docs.craftcms.com/v3/dev/element-queries/)
sort, except you would use one of bookings special sorting handles (see below).

::: warning
This is for sorting regular Craft elements by special bookings parameters. These 
will not work if you are querying availability. 
:::

## Sorting Handles

### `bookings:nextSlot`

Sort by the next available slot of the events.

::: code

```twig
{% set query = craft.products.orderBy('bookings:nextSlot asc').all() %}
```

```php
use craft\commerce\elements\Product;

$query = Product::find()->orderBy('bookings:nextSlot desc')->all();
```

:::