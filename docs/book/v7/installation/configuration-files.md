# Configuration Files

## Prepare config files

* duplicate `config/autoload/cors.local.php.dist` as `config/autoload/cors.local.php`

> If your API is consumed by another application, make sure to configure the `allowed_origins` variable.

* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`

* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php`

> If your API sends emails, make sure to fill in SMTP connection params

* **optional**: to run/create tests, duplicate `config/autoload/local.test.php.dist` as `config/autoload/local.test.php`

> This creates a new in-memory database that your tests will run on.
