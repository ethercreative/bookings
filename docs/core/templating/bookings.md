---
title: Querying Bookings
sidebarDepth: 3
---

# Querying Bookings

[[toc]]

## How to query

You can access your site's bookings from your templates using `craft.bookings`.
This returns an [Element Query](https://docs.craftcms.com/v3/element-queries.html#creating-element-queries).
Unless otherwise specified, most parameters support the [Param Value Syntax](https://docs.craftcms.com/v3/element-queries.html#param-value-syntax).

::: code

```twig
craft.bookings()
```

```php
\ether\bookings\elements\Booking::find()
```

:::

## Parameters

### `status(int)`

Find bookings by the given status. You can view all available statuses [here](./global-variables.md#statuses).

::: code

```twig
{% set query = craft.bookings.status(BOOKING_COMPLETED).all() %}
```

```php
use ether\bookings\elements\Booking;

Booking::find()->status(Booking::STATUS_COMPLETED)->all();
```

:::

### `element(Element|int)`

Find all bookings for a given element or element ID.

::: code

```twig
{% set query = craft.bookings.element(entry).all() %}

{# or by the element ID #}

{% set query = craft.bookings.element(248).all() %}
```

```php
use craft\elements\Entry;
use ether\bookings\elements\Booking;

$entry = Entry::()->section('events')->one();
Booking::find()->element($entry)->all();

// or by the element ID

Booking::find()->element(248)->all();
```

:::

### `email(string)`

Find all bookings for a given email. The parameter must be a string.

::: code

```twig
{% set query = craft.bookings.email('customer@email.com').all() %}
```

```php
use ether\bookings\elements\Booking;

Booking::find()->email('customer@email.com')->all();
```

:::

### `expired(bool)`

Find all [expired](../concepts.md#expiration) bookings. The parameter must be a boolean.

::: code

```twig
{% set query = craft.bookings.expired(false).all() %}
```

```php
use ether\bookings\elements\Booking;

Booking::find()->expired(false)->all();
```

:::

### `field(Field|int)`

Find all bookings for a given field or field ID.

::: code

```twig
{% set bookableField = craft.app.fields.getFieldByHandle('bookable') %} 
{% set query = craft.bookings.field(bookableField).all() %}

{# or by the field ID #}

{% set query = craft.bookings.field(42).all() %}
```

```php
use ether\bookings\elements\Booking;

$bookableField = \Craft::$app->fields->getFieldByHandle('bookable');
Booking::find()->field($bookableField)->all(); 

// or by the field ID

Booking::find()->field(42)->all();
```

:::

### `number(string)`

Find a booking by its unique number. The parameter must be a string.

::: code

```twig
{% set query = craft.bookings.number('b167440d0b5835935e7cc38c521d74f7').one() %}
```

```php
use ether\bookings\elements\Booking;

Booking::find()->number('b167440d0b5835935e7cc38c521d74f7')->one();
```

:::

### `user(User|int)`

Find all bookings for a given user or user ID.

::: code

```twig
{% set query = craft.bookings.user(currentUser).all() %}

{# or by the user ID #}

{% set query = craft.bookings.user(1).all() %}
```

```php
use ether\bookings\elements\Booking;

$currentUser = \Craft::$app->user;
Booking::find()->user($currentUser)->all();

// or by the user ID

Booking::find()->user(1)->all();
```

:::

## Parameters: Dates

If you're trying to find out if a [slot](../concepts.md#slot) is available, check out [Querying Availability](./availability.md).

### `start(DateTime|string|int)`

Find all slots with the given starting time.

```twig
{% set query = craft.bookings.start(now).all() %}
```

## Parameters: Commerce

### `customer(Customer|int)`

Find the bookings for a given customer or customer ID.

::: code

```twig
{% set query = craft.bookings.customer(cart.customer).all() %}
```

```php
use craft\commerce\Plugin as Commerce;
use ether\bookings\elements\Booking;

$cart = Commerce::getInstance()->carts->getCart();
Bookings::find()->customer($cart->customer)->all();
```

:::

### `lineItem(LineItem|int)`

Find the booking for a given line item or line item ID.

::: code

```twig
{% set query = craft.bookings.lineItem(cart.lineItems[0])).one() %}
```

```php
use craft\commerce\Plugin as Commerce;
use ether\bookings\elements\Booking;

$cart = Commerce::getInstance()->carts->getCart();
Bookings::find()->lineItem($cart->lineItems[0])->all();
```

:::

### `order(Order|int)`

Find the bookings for a given order or order ID.

::: code

```twig
{% set query = craft.bookings.order(cart).all() %}
```

```php
use craft\commerce\Plugin as Commerce;
use ether\bookings\elements\Booking;

$cart = Commerce::getInstance()->carts->getCart();
Bookings::find()->lineItem($cart)->all();
```

:::