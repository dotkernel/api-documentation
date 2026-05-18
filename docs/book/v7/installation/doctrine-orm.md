# Doctrine ORM

In this step you will:

- [Save the database connection credentials in the API configuration file](#setup-database).
- [Learn about table names prefixing](#understanding-table-names-prefixing).
- [Create a migration](#creating-migrations).
- [Run a migration](#running-migrations).
- [Executing fixtures to populate your database](#executing-fixtures).

> We do not cover the creation steps of the database itself.

## Setup database

Create a new **MariaDB** or **PostgreSQL** database.
We recommend using a character set that supports UTF-8.

Make sure you fill out the database credentials in `config/autoload/local.php` under `$databases['mariadb']` or `$databases['postgresql']`.
Below is the item you need to focus on:

```php
$databases = [
    'mariadb'    => [
        'host'         => 'localhost',
        'dbname'       => 'dotkernel',
        'user'         => '',
        'password'     => '',
        'port'         => 3306,
        'driver'       => 'pdo_mysql',
        'collation'    => 'utf8mb4_general_ci',
        'table_prefix' => '',
    ],
    'postgresql' => [
        'host'         => 'localhost',
        'dbname'       => 'dotkernel',
        'user'         => '',
        'password'     => '',
        'port'         => 5432,
        'driver'       => 'pdo_pgsql',
        'collation'    => 'utf8mb4_general_ci',
        'table_prefix' => '',
    ],
];
```

> The database `dotkernel` is provided as an example, but you can use any name you like.
> Make sure to use the same database name when you create the database in the next step.

> If needed, you can add more database connections to this array.
> Only **one active database connection** is allowed at a time, decided by the `doctrine.connection.orm_default.params` key in `config/autoload/local.php`.

By default, the application uses the `mariadb` connection, as seen in the `config/autoload/local.php` file below.
You can switch to the 'postgresql' connection by commenting `'params' => $databases['mariadb']` and uncommenting `'params' => $databases['postgresql']`.

```php
'doctrine'            => [
    'connection' => [
        'orm_default' => [
            'params' => $databases['mariadb'],
//                'params' => $databases['postgresql'],
        ],
    ],
],
```

### Understanding Table Names Prefixing

The database configuration array contains an optional key called `table_prefix`.
By default, it is an empty string, which means that all the tables will use the names specified in their respective entities, like below.

```text
├─ admin
├─ admin_login
├─ admin_role
├─ admin_roles
├─ doctrine_migration_versions
├─ oauth_access_tokens
├─ oauth_access_token_scopes
├─ oauth_auth_codes
├─ oauth_auth_code_scopes
├─ oauth_clients
├─ oauth_refresh_tokens
├─ oauth_scopes
├─ settings
├─ user
├─ user_avatar
├─ user_detail
├─ user_reset_password
├─ user_role
└─ user_roles 
```

By adding a prefix, for example `dot_`, all the table names will have the prefix appended to the table names specified in the entities.
This feature helps organize databases and prevent naming conflicts if you plan on installing multiple applications in a single database.

```text
├─ dot_admin
├─ dot_admin_login
├─ dot_admin_role
├─ dot_admin_roles
├─ doctrine_migration_versions
├─ dot_oauth_access_tokens
├─ dot_oauth_access_token_scopes
├─ dot_oauth_auth_codes
├─ dot_oauth_auth_code_scopes
├─ dot_oauth_clients
├─ dot_oauth_refresh_tokens
├─ dot_oauth_scopes
├─ dot_settings
├─ dot_user
├─ dot_user_avatar
├─ dot_user_detail
├─ dot_user_reset_password
├─ dot_user_role
└─ dot_user_roles 
```

> The configured prefix is prepended as is, no intermediary character will be added.

> `doctrine_migration_versions` is an exception and will remain unchanged, since it's a special table handled only by Doctrine Migrations.

### Creating Migrations

When first installing the application, you will need to create a database migration.
Migrations are used to create and update the database schema based on the entities defined in the application.
Later, when you need to update the database schema (e.g., add/remove/edit columns), you will need to create new migrations to reflect the changes.

> Using migration files is recommended compared to manually editing the database schema because they make database changes repeatable, trackable, and safe across environments.

Create a database migration by executing the following command:

```shell
php ./vendor/bin/doctrine-migrations diff
```

You can expect a message like this:

```shell
 Generated new migration class to "src/Core/src/App/src/Migration/Version20260327154303.php"

 To run just this migration for testing purposes, you can use migrations:execute --up "Core\\App\\Migration\\Version20260327154303"

 To revert the migration you can use migrations:execute --down "Core\\App\\Migration\\Version20260327154303"
```

### Running Migrations

Running migrations is the process of applying the changes defined in the migration files to the database.

> Make sure to double-check changes before running migrations, especially when removing columns as this can result in data loss.
> The first migration should be safe, since the database is empty.

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

> The version number `YYYYMMDDHHMMSS` is the timestamp of the migration.

### Executing Fixtures

Fixtures are used to seed the database with initial values.
This basically creates the first records in the database.

> Fixtures should be executed after migrating the database to ensure the tables are created.

> You can edit the initial records if your application demands it, even after running the fixtures.
> For example, you can edit the user roles or the initial users.

> **Important**
>
> Edit the names and passwords of the initial users to prevent unauthorized users from logging into your application.
> Make sure to do so in these files:
>
> - `src/Core/src/App/Fixture/UserLoader.php`.
> - `src/Core/src/App/Fixture/AdminLoader.php`.
>
> Check for these methods and change their default parameters:
>
> - `setIdentity`.
> - `usePassword`.
> - And optionally `setFirstName` and `setLastName`.

To execute fixtures, run:

```shell
php ./bin/doctrine fixtures:execute
```

If everything ran correctly, you will get this confirmation:

```shell
Executing Core\App\Fixture\AdminRoleLoader
Executing Core\App\Fixture\OAuthClientLoader
Executing Core\App\Fixture\OAuthScopeLoader
Executing Core\App\Fixture\UserRoleLoader
Executing Core\App\Fixture\AdminLoader
Executing Core\App\Fixture\UserLoader
Fixtures have been loaded.
                .''
      ._.-.___.' (`\
     //(        ( `'
    '/ )\ ).__. )
    ' <' `\ ._/'\
       `   \     \
```

More details on how fixtures work can be found on [dot-data-fixtures documentation](https://github.com/dotkernel/dot-data-fixtures#usage)
