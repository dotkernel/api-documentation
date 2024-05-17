# Frequently Asked Questions

## How do I fix common permission issues?

If running your project you encounter some permission issues, follow the below steps.

### Errors:

> PHP Fatal error:  Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data" is not writable...

> PHP Fatal error:  Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data/cache" is not writable...

> PHP Fatal error:  Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data/cache/doctrine" is not writable...

**Fix:**

```shell
chmod -R 777 data

### Error:

> PHP Fatal error:  Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/public/uploads" is not writable...

**Fix:**

```shell
chmod -R 777 public/uploads

### Error:

> PHP Fatal error:  Uncaught ErrorException: fopen(/var/www/_example.local_/config/autoload/../../log/error-log-_yyyy-mm-dd.log_): Failed to open stream: Permission denied...

**Fix:**

    chmod -R 777 log