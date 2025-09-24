# Doctrine ORM

## Setup database

Use an existing empty one or create a new **MariaDB**/**MySQL** database.

> Recommended collation: `utf8mb4_general_ci`.

With a database created, fill out the database connection params in `config/autoload/local.php` under `$databases['default']`.

#### Creating migrations

Create a new migration by running:

```shell
php ./vendor/bin/doctrine-migrations diff
```

The new migration file will be placed in `src/Core/src/App/src/Migration/`.

#### Running migrations

Execute a new migration by running:

```shell
php ./vendor/bin/doctrine-migrations migrate
```

This command will prompt you to confirm that you want to run it:

> WARNING! You are about to execute a migration in database "..." that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:

Hit `Enter` to confirm the operation.

## Executing fixtures

**Fixtures are used to seed the database with initial values and should be executed after migrating the database.**

To list all the fixtures, run:

```shell
php ./bin/doctrine fixtures:list
```

This will output all the fixtures in the order of execution.

To execute all fixtures, run:

```shell
php ./bin/doctrine fixtures:execute
```

To execute a specific fixture, run:

```shell
php ./bin/doctrine fixtures:execute --class=FixtureClassName
```

More details on how fixtures work can be found on [dot-data-fixtures documentation](https://github.com/dotkernel/dot-data-fixtures#creating-fixtures)
