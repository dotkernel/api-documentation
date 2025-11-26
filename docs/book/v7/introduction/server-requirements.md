# Server Requirements

For production, we highly recommend a *nix-based system.

## Webserver

### Apache >= 2.2

* mod_rewrite
* .htaccess support `(AllowOverride All)`

> The repository includes a default `.htaccess` file in the `public` folder.

### Nginx

You need to convert the provided Apache related `.htaccess` file into Nginx configuration instructions.

## PHP >= 8.2

Both mod_php and FCGI (FPM) are supported.

## Required Settings and Modules & Extensions

* memory_limit >= 128M
* upload_max_filesize and post_max_size >= 100 M (depending on your data)
* mbstring
* CLI SAPI (for Cron Jobs)
* Composer (added to $PATH)

## RDBMS

* Tested with MariaDB 10.6, 10.11 LTS, 11.4 LTS, and 11.8 LTS
* Tested with PostgreSQL 13 and above

> MySQL is not supported because of missing UUID support.

## Recommended extensions

* `opcache`
* `pdo_mysql`, `pdo_pgsql` or `mysqli` (if using MariaDB or PostgreSQL as RDBMS)
* `dom` - if working with markup files structure (HTML, XML, etc.)
* `simplexml` - working with XML files
* `gd`, `exif` - if working with images
* `zlib`, `zip`, `bz2` - if compressing files
* `curl` (required if APIs are used)
* `sqlite3` - for tests
