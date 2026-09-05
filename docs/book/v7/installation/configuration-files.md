# Configuration Files

## Summary

Composer's `post-update-cmd` scripts create the local configuration files your installation needs: `cors.local.php`, `local.php`, `mail.local.php` and `local.test.php`.
Each one must be reviewed before the application will run correctly, in development and again in production.

## Details

The post-update scripts from `composer.json` (under the key `post-update-cmd`) should have already created the files mentioned on this page.

> We mention these files explicitly because you will need to visit them to fully configure your development environment.
> You will need to do the same for the production environment when you deploy your application.

## Prepare config files

The installation script will duplicate the following files:

* `config/autoload/cors.local.php.dist` as `config/autoload/cors.local.php`.

> If your API is consumed by another application, make sure to configure the `allowed_origins` variable.
> Normally, the other configuration items in `cors.local.php` should be left as-is.
> If you need to tweak them, visit the [CORS tutorial](https://docs.dotkernel.org/api-documentation/v7/tutorials/cors/).

* `config/autoload/local.php.dist` as `config/autoload/local.php`.

> `local.php` is the main configuration file for your application.
> It contains the database connection parameters, the API key, and other configuration items.

* `config/autoload/mail.local.php` from the `dot-mail` package installed in the `vendor` folder.

> If your API sends emails, you also need to configure the `mail` key.
> Most often, you will be using either `Sendmail` or `SMTP` to send emails.
> If you opt for `SMTP`, ake sure to configure the SMTP connection parameters under the `smtp_options` key.

* `config/autoload/local.test.php.dist` as `config/autoload/local.test.php` to run and create tests.

> This creates a new in-memory database that your tests will run on.

## FAQ

**Q: The configuration files are missing. What went wrong?**

A: The `post-update-cmd` scripts did not run.
Run `composer update` (or `composer install` without a lock file) from the project root to trigger them.

**Q: Which file holds the database connection?**

A: `config/autoload/local.php`, which is also where the API key and other environment-specific settings live.

**Q: Should I commit these files to version control?**

A: No.
The `*.local.php` files hold environment-specific values and are excluded from the repository; only the `.dist` templates are tracked.

**Q: When do I need to edit `cors.local.php`?**

A: Whenever another application consumes your API — set `allowed_origins` to the consuming origins.
The remaining options can normally stay as shipped.
See the [CORS tutorial](https://docs.dotkernel.org/api-documentation/v7/tutorials/cors/).

**Q: Where does `mail.local.php` come from?**

A: It is copied from the `dot-mail` package in `vendor`.
Configure the `mail` key, and the `smtp_options` sub-key if you send through SMTP.
See [Rendering and sending emails](../core-features/rendering-and-sending-emails.md).

**Q: Do tests use my development database?**

A: No.
`local.test.php` points the test suite at a separate in-memory database.
See [Test the installation](test-the-installation.md).
