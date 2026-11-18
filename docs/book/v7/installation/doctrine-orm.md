# Doctrine ORM

## Summary

The database step of the installation: enter your MariaDB or PostgreSQL credentials in `config/autoload/local.php`, decide whether to prefix table names, then generate a migration from the entities, run it to build the schema, and execute the fixtures to seed the initial roles, OAuth clients and accounts.

## Details

In this step you will:

- [Save the database connection credentials in the API configuration file](#setup-database).
- [Learn about table names prefixing](#understanding-table-names-prefixing).
- [Decide whether to use persistent connections](#persistent-connections).
- [Decide whether to enable the second-level cache](#second-level-cache).
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
        'persistent'   => false,
        'collation'    => 'utf8mb4_general_ci',
        'table_prefix' => '',
    ],
    'postgresql' => [
        'host'         => '127.0.0.1',
        'dbname'       => 'dotkernel',
        'user'         => '',
        'password'     => '',
        'port'         => 5432,
        'driver'       => 'pdo_pgsql',
        'persistent'   => false,
        'collation'    => 'utf8mb4_general_ci',
        'table_prefix' => '',
    ],
];
```

> Dotkernel API 7.0 ships the PostgreSQL `host` as `localhost`; 7.1 and 7.2 ship `127.0.0.1`, because `localhost` only works for socket connections.
> See [Upgrading from 7.0 to 7.1](../upgrading/UPGRADE-7.1.md).

> The database `dotkernel` is provided as an example, but you can use any name you like.
> Make sure to use the same database name when you create the database in the next step.

> If needed, you can add more database connections to this array.
> Only **one active database connection** is allowed at a time, decided by the `doctrine.connection.orm_default.params` key in `config/autoload/local.php`.

By default, the application uses the `mariadb` connection, as seen in the `config/autoload/local.php` file below.
You can switch to the 'postgresql' connection by commenting `'params' => $databases['mariadb']` and uncommenting `'params' => $databases['postgresql']`.

```php
'doctrine'            => [
    'connection'    => [
        'orm_default' => [
            'params' => $databases['mariadb'],
//                'params' => $databases['postgresql'],
        ],
    ],
    'configuration' => [
        // second-level cache, see below
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

### Second-level cache

Starting with `doctrine/orm` 3.8.0, Doctrine's second-level cache needs the separate package `doctrine/orm-plugin-second-level-cache`.
The ORM only suggests that package, so Composer does not install it.
If the cache is enabled and the package is missing, the entity manager cannot be created and the first request fails with:

```text
Service with name "doctrine.entity_manager.orm_default" could not be created.
Reason: Using the second level cache requires installing an additional package.
Install with composer require doctrine/orm-plugin-second-level-cache
```

The Core `ConfigProvider` turns the cache on by default.
`config/autoload/local.php` switches it off by setting the `enabled` key to `false`.
It loads after the Core configuration and overrides only that key, so the other second-level cache settings still come from Core:

```php
'doctrine' => [
    'connection'    => [
        // ...
    ],
    'configuration' => [
        'orm_default' => [
            'second_level_cache' => [
                'enabled' => false,
            ],
        ],
    ],
],
```

The stock application does not use the second-level cache: no entity has `#[ORM\Cache]` and no query calls `setCacheable(true)`.
The query cache used by the repositories (`useQueryCache(true)`) is a separate cache and is not affected by this flag.

To enable the second-level cache, install the plugin and then set `enabled` to `true`:

```shell
composer require doctrine/orm-plugin-second-level-cache:^1.0
```

> `config/autoload/local.php` is copied from `local.php.dist` only once, at install time.
> If your installation predates this setting, add the `configuration` block to your `local.php` by hand.
> If configuration caching is enabled, delete `data/cache/config-cache.php` afterwards.

### Persistent connections

Each entry in `$databases` has a `persistent` key, which is `false` by default.
Set it to `true` on the connection you use to keep the database connection open after a request ends, so the next request handled by the same PHP process reuses it instead of opening a new one.

| Driver | `persistent` supported |
| --- | --- |
| `pdo_mysql`, `mysqli` | Yes |
| `pdo_pgsql` | Yes |
| `pgsql` | No |
| `pdo_sqlite`, `sqlite3` | No, the key is ignored |

> Persistent connections for MySQL and PostgreSQL are first documented in `doctrine/dbal` 4.3 and 3.10.
> `doctrine/dbal` is installed as a dependency of other packages, so run `composer show doctrine/dbal` to check your version.
> Older versions ignore the key.

Before you turn it on, check the following:

- **Connection limit:** every PHP-FPM worker holds its own connection, so the database's `max_connections` must be higher than the total number of workers across every pool and server that connects to it.
- **Leftover state:** a reused connection keeps whatever the previous request left on it, such as an open transaction after a fatal error, session variables, temporary tables or locks.
- **Idle timeouts:** the database can close a connection that has been idle too long (MariaDB's `wait_timeout`), and the next request on that worker then fails with "server has gone away".
- **CLI:** commands gain nothing, because each one runs in its own process.
- **PostgreSQL:** a connection pooler such as PgBouncer is usually a better choice than persistent connections.

To confirm that connections are reused, make a few requests and compare the connection IDs:

```sql
-- MariaDB
SHOW PROCESSLIST;

-- PostgreSQL
SELECT pid, backend_start FROM pg_stat_activity WHERE datname = 'dotkernel';
```

> If your installation predates this setting, add `'persistent' => false,` to each entry in `$databases` in your `local.php` by hand.

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

### Important Security Concern

The following section deals with fixtures which create the first user and role records in the database.
We recommend you **edit the names and passwords** found in the files below to prevent unauthorized users from logging into your application:

- `src/Core/src/App/Fixture/UserLoader.php`.
- `src/Core/src/App/Fixture/AdminLoader.php`.

Look for these methods and change their current parameters:

- `setIdentity` for the **username**,
- `usePassword` for the **password**,
- and optionally `setFirstName` and `setLastName`.

### Executing Fixtures

Fixtures are used to seed the database with initial values.
This basically creates the first records in the database.

> Fixtures should be executed after migrating the database to ensure the tables are created.

> You can edit the initial records if your application demands it, even after running the fixtures.
> For example, you can edit the user roles or the initial users.

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

## FAQ

**Q: Where do I put the database credentials?**

A: In `config/autoload/local.php`, under `$databases['mariadb']` or `$databases['postgresql']`.

**Q: How do I switch from MariaDB to PostgreSQL?**

A: Comment out `'params' => $databases['mariadb']` and uncomment the `postgresql` line under `doctrine.connection.orm_default`.
Only one active connection is allowed at a time, even if the array defines several.

**Q: Do I have to name the database `dotkernel`?**

A: No.
That is only an example — use any name, as long as the configuration and the database you create agree.

**Q: What is `table_prefix` for?**

A: It prepends a string to every table name from the entities, which keeps table sets apart when several applications share one database.
The prefix is applied verbatim, with no separator added, and `doctrine_migration_versions` is left untouched because Doctrine Migrations owns it.

**Q: Should I enable persistent connections?**

A: Only after checking that the database's `max_connections` covers every PHP-FPM worker that connects to it.
They save the cost of opening a connection on each request, but each worker then holds a connection permanently.
See [Persistent connections](#persistent-connections).

**Q: I set `persistent` to `true`, but every request still opens a new connection. Why?**

A: Check that you changed the entry that `doctrine.connection.orm_default.params` points to, that the driver is `pdo_mysql`, `mysqli` or `pdo_pgsql`, and that `doctrine/dbal` is at least 4.3 (or 3.10).
If configuration caching is enabled, delete `data/cache/config-cache.php`.

**Q: The first request fails with "Using the second level cache requires installing an additional package". What is wrong?**

A: Composer installed `doctrine/orm` 3.8.0 or later, which needs `doctrine/orm-plugin-second-level-cache` for the second-level cache, and your configuration still enables that cache.
Set `doctrine.configuration.orm_default.second_level_cache.enabled` to `false` in `config/autoload/local.php`, or install the plugin if you need the cache.
See [Second-level cache](#second-level-cache).

**Q: Does disabling the second-level cache slow the application down?**

A: Not in the stock application, which never stores entities in it.
It only matters if you add `#[ORM\Cache]` to entities or call `setCacheable(true)` on queries.

**Q: Why use migrations instead of editing the schema by hand?**

A: Migrations make schema changes repeatable, trackable and safe to apply across environments.

**Q: Which command creates a migration, and which applies it?**

A: `php ./vendor/bin/doctrine-migrations diff` generates one from the current entities; `php ./vendor/bin/doctrine-migrations migrate` applies all pending migrations in chronological order.

**Q: Migrating warns about previously executed migrations that are not registered. Should I continue?**

A: Check what the listed migrations would do first.
The warning means the database records migrations the codebase does not know about, which is worth understanding before proceeding.

**Q: Will migrating drop tables I created outside Doctrine?**

A: `diff` proposes dropping unmapped tables such as the `oauth_*` ones.
Use a filter expression to exclude them.
See [Generate a database migration without dropping custom tables](../commands/generate-database-migrations.md).

**Q: What do the fixtures create?**

A: The admin and user roles, the OAuth clients and scopes, and the initial admin and user accounts.

**Q: Must fixtures run after migrations?**

A: Yes.
The tables have to exist before they can be populated.

**Q: How do I change the seeded credentials?**

A: Edit `setIdentity`, `usePassword` and optionally `setFirstName` and `setLastName` in `src/Core/src/App/Fixture/UserLoader.php` and `AdminLoader.php` before running the fixtures.
Leaving the defaults in place lets anyone log in.
See [Basic security](../security/basic-security.md).

**Q: Can I still change the seeded records afterwards?**

A: Yes.
Roles and initial users can be edited after the fixtures have run.
