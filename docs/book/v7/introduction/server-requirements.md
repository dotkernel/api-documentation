# Server Requirements

## Summary

What Dotkernel API v7 needs to run: a Linux host in production (Windows via WSL2 for development), Apache with `mod_rewrite` or an Nginx equivalent, PHP 8.2 or newer with `mbstring` and CLI SAPI, Composer 2.0 or newer, and MariaDB or PostgreSQL — MySQL is not supported because it lacks native UUID types.
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

## PHP >= 8.2

Dotkernel API v7 requires PHP 8.2 or higher.
Earlier PHP versions are not supported.

### Supported PHP Configurations

- FPM (FastCGI Process Manager) - Recommended for production, better performance and security isolation
- FastCGI - Obsolete but still in use by some hosting providers
- CLI SAPI (Command Line Interface) - Required for Cron jobs, migrations, and fixtures

### Why PHP 8.2+?

Dotkernel API leverages modern PHP features:

- Named Arguments: Clearer function calls
- Match Expressions: More readable than switch statements
- Attributes: Metadata for dependency injection and OpenAPI documentation
- Union Types: Better type safety
- Nullsafe Operator: Safer null handling
- Constructor Property Promotion: Cleaner code

## Required Settings and Modules & Extensions

- memory_limit >= 128M
- upload_max_filesize and post_max_size >= 100 M (depending on your data)
- mbstring
- CLI SAPI (for Cron Jobs)
- Composer (added to $PATH)

## Relational Database Management System (RDBMS)

> MySQL is NOT supported because it lacks native UUID support (required by Dotkernel API v7).
> MariaDB and PostgreSQL both have native UUID types and functions.

### MariaDB

Tested with:

- MariaDB 10.7
- MariaDB 10.11 LTS (Long-Term Support)
- MariaDB 11.4 LTS
- MariaDB 11.8 LTS

> It's recommended to use LTS versions for stability and security updates.

### PostgreSQL

Tested with:

- PostgreSQL 13 and above

### Database Collation

When creating databases, use:

- MariaDB: utf8mb4_general_ci or utf8mb4_unicode_ci
- PostgreSQL: C.UTF-8 or en_US.UTF-8

## Recommended extensions

- `opcache`
- `pdo_mysql`, `pdo_pgsql` or `mysqli` (if using MariaDB or PostgreSQL as RDBMS)
- `dom` - if working with markup files structure (HTML, XML, etc.)
- `simplexml` - working with XML files
- `gd`, `exif` - if working with images
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

A: MariaDB 10.7, 10.11 LTS, 11.4 LTS and 11.8 LTS, and PostgreSQL 13 and above.
LTS releases are recommended for stability and security updates.

**Q: What collation should I create the database with?**

A: `utf8mb4_general_ci` or `utf8mb4_unicode_ci` on MariaDB, and `C.UTF-8` or `en_US.UTF-8` on PostgreSQL.

**Q: What is the minimum PHP version?**

A: PHP 8.2.
Earlier versions are not supported, because the project relies on named arguments, match expressions, attributes, union types, the nullsafe operator and constructor property promotion.

**Q: Do I need the CLI SAPI as well as FPM?**

A: Yes.
FPM (or FastCGI) serves web requests, while the CLI SAPI is required for cron jobs, migrations and fixtures.

**Q: Can I run this on Nginx?**

A: Yes, but the shipped `.htaccess` in `public` is Apache-specific — you must translate its rewrite rules into Nginx configuration.

**Q: Can I develop on Windows?**

A: Yes, through WSL2. macOS and Linux are also supported for development; production should be Linux.

**Q: Which PHP extensions are actually required?**

A: `mbstring` plus the PDO driver for your database.
The rest — `opcache`, `gd`, `curl`, `zip`, `dom`, `simplexml`, `sqlite3` and others — depend on what your application does, with `sqlite3` needed to run the tests.

**Q: What are the baseline hardening steps?**

A: Expose only ports 80 and 443, disable `exec`, `shell_exec`, `passthru` and `system` in PHP, use strong database passwords with restricted permissions, set 644 on files and 755 on directories, and keep the stack patched.
See [Basic security](../security/basic-security.md).
