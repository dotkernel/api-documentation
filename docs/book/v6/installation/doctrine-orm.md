# Doctrine ORM

This step saves the database connection credentials in an API configuration file.
We do not cover the creation steps of the database itself.

## Setup database

Create a new **MariaDB**/**MySQL** database and set its collation to `utf8mb4_general_ci`.

Make sure you fill out the database credentials in `config/autoload/local.php` under `$databases['default']`.
Below is the item you need to focus on:

```php
$databases = [
    'default' => [
        'host'     => 'localhost',
        'dbname'   => 'my_database',
        'user'     => 'my_user',
        'password' => 'my_password',
        'port'     => 3306,
        'driver'   => 'pdo_mysql',
        'charset'  => 'utf8mb4',
        'collate'  => 'utf8mb4_general_ci',
    ],
    // you can add more database connections into this array
];
```

> `my_database`, `my_user`, `my_password` are provided only as an example.

### Creating migrations

Create a database migration by executing the following command:

```shell
php ./vendor/bin/doctrine-migrations diff
```

The new migration file will be placed in `src/Core/src/App/src/Migration/`.

### Running migrations

Run the database migrations by executing the following command:

```shell
php ./vendor/bin/doctrine-migrations migrate
```

> If you have already run the migrations, you may get the below message:

```text
WARNING! You have x previously executed migrations in the database that are not registered migrations.
  {migration list}
Are you sure you wish to continue? (y/n)
```

> In this case, you should double-check to make sure the new migrations are ok to run.

When using an empty database, you will get this confirmation message:

```text
WARNING! You are about to execute a migration in database "<your_database_name>" that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no)
```

Hit `Enter` to confirm the operation.
This will run all the migrations in chronological order.
Each migration will be logged in the `migrations` table to prevent running the same migration more than once, which is often not desirable.

If everything ran correctly, you will get this confirmation.

```text
[OK] Successfully migrated to version: Core\App\Migration\VersionYYYYMMDDHHMMSS
```

### Executing fixtures

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
