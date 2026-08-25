# Configuration Files

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
