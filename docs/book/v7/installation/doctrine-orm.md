# Doctrine ORM

This step saves the database connection credentials in an API configuration file.
We do not cover the creation steps of the database itself.

In this step you will:

- Create a database.
- Create and run a database migration that creates the main tables.
- Execute fixtures which populate the database with initial data.

## Setup database

Create a new **MariaDB**/**PostgreSQL** database and set its collation to `utf8mb4_general_ci`.

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
> Only **one active database connection** is allowed at a time.

> By default, the application uses the 'mariadb' connection.
> You can switch to another connection by updating `doctrine` -> `connection` -> `orm_default` -> `params`.

### Prefixing table names

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

### Creating migrations

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

> The version number `YYYYMMDDHHMMSS` is the timestamp of the migration.

### Executing fixtures

**Fixtures are used to seed the database with initial values and should be executed after migrating the database.**

To execute fixtures, run:

```shell
php ./bin/doctrine fixtures:execute
```

More details on how fixtures work can be found on [dot-data-fixtures documentation](https://github.com/dotkernel/dot-data-fixtures#usage)
