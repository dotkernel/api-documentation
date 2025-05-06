# Introduction

Below is a quick overview of features in Dotkernel API.

## Doctrine 3 ORM

For the persistence in a relational database management system we chose Doctrine ORM (object-relational mapper).

The benefit of Doctrine for the programmer is the ability to focus on the object-oriented business logic and worry about persistence only as a secondary priority.

## Documentation

Our documentation is Postman based.
We use the following files in which we store information about every available endpoint ready to be tested:

* documentation/Dotkernel_API.postman_collection.json
* documentation/Dotkernel_API.postman_environment.json

## Hypertext Application Language

For our API payloads (a value object for describing the API resource, its relational links and any embedded/child resources related to it) we use [mezzio/mezzio-hal](https://github.com/mezzio/mezzio-hal).

## CORS

By using `MezzioCorsMiddlewareCorsMiddleware`, the CORS preflight will be recognized and the middleware will start to detect the proper CORS configuration.
The Router is used to detect every allowed request method by executing a route match with all possible request methods.
Therefore, for every preflight request, there is at least one Router request.

## OAuth 2.0

OAuth 2.0 is an authorization framework that enables applications to obtain limited access to user accounts on your Dotkernel API.
We use [mezzio/mezzio-authentication-oauth2](https://github.com/mezzio/mezzio-authentication-oauth2) which provides OAuth 2.0 authentication for Mezzio and PSR-15 applications by using the [thephpleague/oauth2-server]https://github.com/thephpleague/oauth2-server package.

## Email

It is not unlikely for an API to send emails depending on the use case.
Here is another area where Dotkernel API shines.
Using `DotMailServiceMailService` provided by [dotkernel/dot-mail](https://github.com/dotkernel/dot-mail) you can easily send custom email templates.

## Configuration

From authorization at request route level to API keys for your application, you can find every configuration variable in the `config` directory.

Registering a new module can be done by including its `ConfigProvider.php` in `config.php`.

Brand new middlewares should go into `pipeline.php`. Here you can edit the order in which they run and find more info about the currently included ones.

You can further customize your api within the `autoload` directory that holds configuration files for each category.

## Routing

Each module has a `RoutesDelegator.php` file for managing existing routes inside that specific module.
It also allows a quick way of adding new routes by providing the route path, Middlewares that the route will use and the route name.

You can allocate permissions per route name in order to restrict access for a user role to a specific route in `config/autoload/authorization.global.php`.

## Commands

For registering new commands first make sure your command class extends `Symfony\Component\Console\Command\Command`.
Then you can enable it by registering it in `config/autoload/cli.global.php`.

## File locker

Here you will also find our file locker configuration, so you can easily enable and disable it (by default: `'enabled' => true`).

Note: The File Locker System will create a `command-{command-default-name}.lock` file which will not let another instance of the same command to run until the previous one has finished.

## Tests

One of the best ways to ensure the quality of your product is to create and run functional and unit tests.
You can find factory-made tests in the `test` folder, and you can also register your own.

We have 2 types of tests: functional and unit tests.
You can run both types at the same type by executing this command:

```shell
php vendor/bin/phpunit
```

Alternatively, you can run each test category separately with these commands:

```shell
vendor/bin/phpunit --testsuite=UnitTests --testdox --colors=always
vendor/bin/phpunit --testsuite=FunctionalTests --testdox --colors=always
```
