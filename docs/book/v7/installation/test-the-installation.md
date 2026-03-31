# Test the installation

> If you are getting server error 500, make sure to check the folder permissions covered in the [FAQ page](https://docs.dotkernel.org/api-documentation/v7/installation/faq/)

Sending a GET request to the home page for your virtual host should output the following message:

```json
{"message": "Dotkernel API version 7"}
```

From this point on, you are ready to add custom code to your application.

## Old way of doing things, using PHP built-in server

This is an alternative way of accessing the API, without using a virtual host.
Run this command from the project root:

```shell
php -S 0.0.0.0:8080 -t public
```

You should see something like this:

```shell
[Mon Mar 30 14:53:50 2026] PHP 8.4.15 Development Server (http://0.0.0.0:8080) started
```

## Running tests

The project has two types of tests: functional and unit tests, you can run both types at the same type by executing this command:

```shell
php vendor/bin/phpunit
```

## Running unit tests

```shell
vendor/bin/phpunit --testsuite=UnitTests --testdox --colors=always
```

## Running functional tests

```shell
vendor/bin/phpunit --testsuite=FunctionalTests --testdox --colors=always
```
