---
title: Availability
sidebarDepth: 3
---

# Availability

## How to Query

You can query availability via an API request.

```javascript 1.8

fetch("/", {
	method: "POST",
	mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
        "Content-Type": "application/json; charset=utf-8",
        "Accept": "application/json",
    },
    body: JSON.stringify({
        [window.csrfTokenName()]: window.csrfTokenValue(),
        action: "bookings/availability",
        eventId: 12,
        start: new Date(), // Optional
        end: new Date(), // Optional
        limit: 100, // Optional
        group: 'week', // Optional
    })
}).then(res => res.json());

```
