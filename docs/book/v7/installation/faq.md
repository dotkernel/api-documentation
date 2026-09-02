# Frequently Asked Questions

## Summary

Solutions to the permission errors most often seen right after installation.
Each one comes from a directory the application needs to write to — `data`, `data/cache`, `public/uploads` or `log` — and is fixed by granting write access to that directory.

## How do I fix common permission issues?

If running your project, you encounter some permission issues, follow the below steps.

### Errors

> PHP Fatal error: Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data" is not writable...

> PHP Fatal error: Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data/cache" is not writable...

> PHP Fatal error: Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/data/cache/doctrine" is not writable...

**Fix:**

```shell
chmod -R 777 data
```

### Error

> PHP Fatal error: Uncaught InvalidArgumentException: The directory "/var/www/_example.local_/html/public/uploads" is not writable...

**Fix:**

```shell
chmod -R 777 public/uploads
```

### Error

> PHP Fatal error: Uncaught ErrorException: fopen(/var/www/_example.local_/config/autoload/../../log/error-log-_yyyy-mm-dd.log_): Failed to open stream: Permission denied...

**Fix:**

```shell
chmod -R 777 log
```

## FAQ

**Q: Why do these errors appear right after installation?**

A: The application writes caches to `data`, uploaded files to `public/uploads` and log files to `log`.
If those directories are not writable by the web server user, the first request that needs them fails.

**Q: Which directories need write access?**

A: `data` (including `data/cache` and `data/cache/doctrine`), `public/uploads` and `log`.
Applying the permissions recursively with `-R` covers the subdirectories.

**Q: Is granting `777` safe?**

A: Access to application files is governed by the `.htaccess` rules: only `public` is served directly and everything else is routed through `index.php`, so these writable folders are not reachable from the web.
See [Clone the project](getting-started.md).

**Q: I get a blank `500` response with no message. Where do I look?**

A: Check the web server error log first, since a log directory that is not writable prevents the application from recording the error itself.
Fixing the `log` permissions usually makes the real message appear.

**Q: Are there other common installation problems besides permissions?**

A: Yes — missing configuration files and an unmigrated database.
See [Configuration files](configuration-files.md) and [Doctrine ORM](doctrine-orm.md).
