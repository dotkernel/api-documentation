# Doctrine ORM

## Setup database

Make sure you fill out the database credentials in `config/autoload/local.php` under `$databases['default']`.

Create a new MySQL database - set collation to `utf8mb4_general_ci`

## Running migrations

Run the database migrations by using the following command:

```shell
php vendor/bin/doctrine-migrations migrate
```

This command will prompt you to confirm that you want to run it.

> WARNING! You are about to execute a migration in database "..." that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:

Hit `Enter` to confirm the operation.

## Executing fixtures

**Fixtures are used to seed the database with initial values and should be executed after migrating the database.**

To list all the fixtures, run:

```shell
php bin/doctrine fixtures:list
```

This will output all the fixtures in the order of execution.

To execute all fixtures, run:

```shell
php bin/doctrine fixtures:execute
```

To execute a specific fixture, run:

```shell
php bin/doctrine fixtures:execute --class=FixtureClassName
```

More details on how fixtures work can be found on [dot-data-fixture documentation](https://github.com/dotkernel/dot-data-fixtures#creating-fixtures)
