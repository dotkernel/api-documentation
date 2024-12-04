# Server Requirements

For production, we highly recommend a *nix based system.

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
* upload_max_filesize and post_max_size >= 100M (depending on your data)
* mbstring
* CLI SAPI (for Cron Jobs)
* Composer (added to $PATH)

## RDBMS

* Tested with MariaDB 10.11 LTS and MariaDB 11.4 LTS
* Tested with MySQL 8.4 LTS

> :exclamation: For MySQL 8.4 LTS be sure you have the below line in my.cnf

```text
mysql_native_password=ON
```

## Recommended extensions

* opcache
* pdo_mysql or mysqli (if using MySQL or MariaDB as RDBMS)
* dom - if working with markup files structure (html, xml, etc)
* simplexml - working with xml files
* gd, exif - if working with images
* zlib, zip, bz2 - if compessing files
* curl (required if APIs are used)
* sqlite3 - for tests
