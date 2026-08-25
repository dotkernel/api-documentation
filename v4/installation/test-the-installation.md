# Test the installation

Sending a GET request to the [home page](http://0.0.0.0:8080/) should output the following message:

> {"message": "Dotkernel API version 4"}

## Old way of doing things, using PHP built-in server

```shell
php -S 0.0.0.0:8080 -t public
```

## Running tests

The project has 2 types of tests: functional and unit tests, you can run both types at the same type by executing this
command:

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
