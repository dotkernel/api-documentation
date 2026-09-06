# Server Requirements

## Summary

What Dotkernel API v7 needs to run: a Linux host in production (Windows via WSL2 for development), Apache with `mod_rewrite` or an Nginx equivalent, PHP 8.3, 8.4 or 8.5 with the `gd`, `json` and `mbstring` extensions and CLI SAPI, Composer 2.0 or newer, and MariaDB 11.4 LTS or newer or PostgreSQL 13 or newer — MySQL is not supported because it lacks native UUID types.
Recommended extensions and baseline server hardening are listed as well.

## Details

For production environments, we highly recommend a Linux-based system.
Windows is supported for development via WSL2.

## Operating System

### Production

- Linux (AlmaLinux, Debian)

### Development

- Windows 10/11 (via WSL2 - see our [WSL2 Setup Guide](https://docs.dotkernel.org/development/v2/setup/installation/))
- macOS
- Linux

> We recommend a Linux-based environment for production because of its improved performance, stability, and security hardening options compared to Windows Server.
> It should also work on Microsoft's IIS server with minimal modifications, but we have not tested this setup in our projects.

## Webserver

### Apache >= 2.2

- mod_rewrite
- .htaccess support `(AllowOverride All)`

> The repository includes a default `.htaccess` file in the `public` folder.

### Nginx

You need to convert the provided Apache related `.htaccess` file into Nginx configuration instructions.

## PHP 8.3, 8.4 or 8.5

Dotkernel API v7 requires PHP 8.3, 8.4 or 8.5, as declared in `composer.json`:

```json
"require": {
    "php": "~8.3.0 || ~8.4.0 || ~8.5.0"
}
```

Earlier PHP versions are not supported.
Support for PHP 8.2 was dropped in Dotkernel API 7.2.0.

> The constraint uses `~` per minor version rather than `>=`, so each supported branch is listed explicitly and a newly released PHP version is not assumed to work until it has been tested.

### Supported PHP Configurations

- FPM (FastCGI Process Manager) - Recommended for production, better performance and security isolation
- FastCGI - Obsolete but still in use by some hosting providers
- CLI SAPI (Command Line Interface) - Required for Cron jobs, migrations, and fixtures

### Why PHP 8.3+?

The floor is set by **typed class constants**, a PHP 8.3 feature used throughout the codebase — for example in `Api\App\Middleware\ContentNegotiationMiddleware`:

```php
public const string DEFAULT_HEADERS = 'default';
```

The project will not even parse on PHP 8.2.

## Required Settings and Modules & Extensions

These extensions are declared in the `require` section of `composer.json` as `ext-gd` and `ext-json`, so Composer refuses to install the project without them:

- `gd` - must be enabled; this is the one to check on a new server
- `json` - ships enabled and cannot be disabled on any supported PHP version, so in practice it needs no action

Also required:

- mbstring
- the PDO driver for your database - `pdo_mysql` for MariaDB or `pdo_pgsql` for PostgreSQL
- memory_limit >= 128M
- upload_max_filesize and post_max_size >= 100 M (depending on your data)
- CLI SAPI (for Cron Jobs)
- Composer (added to $PATH)

## Relational Database Management System (RDBMS)

> MySQL is NOT supported because it lacks native UUID support (required by Dotkernel API v7).
> MariaDB and PostgreSQL both have native UUID types and functions.

### MariaDB

**MariaDB 11.4 LTS is the minimum supported version.**

Tested with:

- MariaDB 11.4 LTS (Long-Term Support)
- MariaDB 11.8 LTS
- MariaDB 12.3 LTS

All three are LTS releases, which we recommend for stability and security updates.

#### Why 11.4 is the minimum

Dotkernel API stores every entity identifier in a MariaDB `UUID` column and generates the value as a **UUIDv7** in PHP, via `Ramsey\Uuid\Uuid::uuid7()`.
UUIDv7 is time-ordered by design, so sequential inserts land next to each other in the index.

MariaDB, however, does not always store a `UUID` in the order it was given.
It rearranges the value internally into an index-friendly layout that assumes a UUIDv1 — where the node comes first and the timestamp second.
Applied to a UUIDv7, that rearrangement scrambles exactly the ordering the type was chosen for.

MariaDB 10.10 changed this: from that release on, UUIDv6 and later are stored in their native order, with no byte-swapping.
The change did not reach the older maintenance series until 10.10.7 and 10.11.6, so "MariaDB 10.11" is only correct from 10.11.6 onward.

11.4 LTS is therefore the earliest LTS series in which *every* patch release stores UUIDv7 natively, which is why it is the published floor.

> On MariaDB 10.7, or on 10.11.0 - 10.11.5, the application still runs: inserts and reads succeed.
> The identifiers are simply stored in scrambled order, so you lose the insert locality UUIDv7 exists to provide.
> It is a silent performance problem rather than an error, which is what makes it worth stating explicitly.

For the background on why identifiers are generated as UUIDv7 in PHP rather than delegated to the database, see [Version 7 adds PostgreSQL, native UUID and PHP 8.5](https://www.dotkernel.com/headless-platform/version-7-adds-postgresql-native-uuid-and-php-8-5/).
Generating them in the application keeps full control over which UUID version is used and avoids depending on a database extension or a particular server version to produce the value.

### PostgreSQL

Tested with:

- PostgreSQL 13 and above

### Database Collation

When creating databases, use:

- MariaDB: utf8mb4_general_ci or utf8mb4_unicode_ci
- PostgreSQL: C.UTF-8 or en_US.UTF-8

## Recommended extensions

These are optional and depend on what your application does.
For the extensions the project itself requires, see the "Required Settings and Modules & Extensions" section above.

- `opcache`
- `dom` - if working with markup files structure (HTML, XML, etc.)
- `simplexml` - working with XML files
- `exif` - additional image metadata handling (`gd` itself is required, not optional)
- `zlib`, `zip`, `bz2` - if compressing files
- `curl` (required if APIs are used)
- `sqlite3` - for tests

## Composer

Dotkernel API requires Composer >= 2.0 for managing PHP dependencies.

## Security Considerations

- **Firewall**: Only expose ports 80 (HTTP) and 443 (HTTPS)
- **PHP**: Disable dangerous functions: exec, shell_exec, passthru, system
- **Database**: Use strong passwords, restrict user permissions
- **Files**: Set proper permissions (644 for files, 755 for directories)
- **Updates**: Keep PHP, web server, and database updated with security patches

## FAQ

**Q: Why is MySQL not supported?**

A: Dotkernel API v7 stores identifiers as native UUIDs, and MySQL has no native UUID type or functions.
MariaDB and PostgreSQL both do.

**Q: Which database versions are tested?**

A: MariaDB 11.4 LTS, 11.8 LTS and 12.3 LTS, and PostgreSQL 13 and above.
MariaDB 11.4 is also the minimum: earlier releases byte-swap `UUID` values and destroy the ordering of the UUIDv7 identifiers this project uses.
See "Why 11.4 is the minimum" above.

**Q: What collation should I create the database with?**

A: `utf8mb4_general_ci` or `utf8mb4_unicode_ci` on MariaDB, and `C.UTF-8` or `en_US.UTF-8` on PostgreSQL.

**Q: What is the minimum PHP version?**

A: PHP 8.3.
`composer.json` requires `~8.3.0 || ~8.4.0 || ~8.5.0`, so 8.3, 8.4 and 8.5 are supported and earlier versions are not.
PHP 8.2 support was dropped in Dotkernel API 7.2.0.
The hard blocker is typed class constants, a PHP 8.3 feature used throughout the codebase, so the project will not parse on 8.2.

**Q: Do I need the CLI SAPI as well as FPM?**

A: Yes.
FPM (or FastCGI) serves web requests, while the CLI SAPI is required for cron jobs, migrations and fixtures.

**Q: Can I run this on Nginx?**

A: Yes, but the shipped `.htaccess` in `public` is Apache-specific — you must translate its rewrite rules into Nginx configuration.

**Q: Can I develop on Windows?**

A: Yes, through WSL2. macOS and Linux are also supported for development; production should be Linux.

**Q: Which PHP extensions are actually required?**

A: `gd` and `json` are declared in `composer.json` as `ext-gd` and `ext-json`, so Composer will not install the project without them.
Add `mbstring` and the PDO driver for your database — `pdo_mysql` or `pdo_pgsql`.
The rest — `opcache`, `curl`, `zip`, `dom`, `simplexml`, `exif`, `sqlite3` and others — depend on what your application does, with `sqlite3` needed to run the tests.

**Q: What are the baseline hardening steps?**

A: Expose only ports 80 and 443, disable `exec`, `shell_exec`, `passthru` and `system` in PHP, use strong database passwords with restricted permissions, set 644 on files and 755 on directories, and keep the stack patched.
See [Basic security](../security/basic-security.md).
