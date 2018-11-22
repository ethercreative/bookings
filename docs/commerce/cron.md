---
title: CRON
---

# CRON

Runs everything required to keep the booking data up-to-date.  
This should be run as a CRON every minute.

```
* * * * * /path/to/site/craft bookings >> /dev/null 2>&1
```