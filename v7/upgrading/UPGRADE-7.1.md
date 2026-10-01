# Upgrading from 7.0 to 7.1

## Summary

The changes you need to port into your project when moving from Dotkernel API 7.0 to 7.1, each linked to the pull request that introduced it.
The headline items are the new PHP 8.3 minimum, the upgrade to `mezzio-authentication-oauth2` 3.x, and the schema change to the admin login log.

## Details

> You can find the release notes in [7.1.0](https://github.com/dotkernel/api/releases/tag/7.1.0) and a complete list in [Changelog](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md)

### Important updates

These changes affect your PHP version, dependencies, configuration, database schema, entities or runtime behavior.

* Upgrade `mezzio/mezzio-authentication-oauth2` to `^3.0.1`: the OAuth entities and repositories are adapted, `Admin` and `NumericIdentifierTrait` types are tightened, and the post-install script now generates `config/autoload/mail.local.php` [https://github.com/dotkernel/api/pull/511](https://github.com/dotkernel/api/pull/511)
* Bump PHPUnit to `^12.5.23` and drop PHP 8.2 support: `composer.json` now requires PHP `~8.3.0 || ~8.4.0 || ~8.5.0` and tests use stubs instead of mocks [https://github.com/dotkernel/api/pull/510](https://github.com/dotkernel/api/pull/510)
* Implement browscap in `AdminLogin`: the columns `deviceBrand`, `deviceModel`, `osPlatform`, `clientEngine` and `clientVersion` are removed and `isCrawler` is added, so a schema migration is required [https://github.com/dotkernel/api/pull/513](https://github.com/dotkernel/api/pull/513)
* Remove `config/autoload/mail.global.php`: mail settings now live in `mail.local.php` [https://github.com/dotkernel/api/pull/499](https://github.com/dotkernel/api/pull/499)
* OAuth2 token invalidated on Composer install/update: adds `bin/generate-oauth2-keys.php`, which skips existing keys, and points `post-update-cmd` to it [https://github.com/dotkernel/api/pull/506](https://github.com/dotkernel/api/pull/506)
* Add security headers in `config/autoload/response-header.global.php`: `X-Content-Type-Options` and `Referrer-Policy` on all responses, `Cache-Control: no-store` and `Pragma: no-cache` on the token endpoints [https://github.com/dotkernel/api/pull/490](https://github.com/dotkernel/api/pull/490)
* Add `MalformedRequestBodyMiddleware`, piped in `config/pipeline.php`: a malformed request body now results in a `BadRequestException` [https://github.com/dotkernel/api/pull/476](https://github.com/dotkernel/api/pull/476)
* Require `symfony/var-exporter` to keep LazyGhost support [https://github.com/dotkernel/api/pull/485](https://github.com/dotkernel/api/pull/485)
* Core sync: `created` becomes nullable in `TimestampsTrait` and `EntityInterface`, and the Admin, User and role entities are updated [https://github.com/dotkernel/api/pull/486](https://github.com/dotkernel/api/pull/486)
* Bump `zircote/swagger-php` to `^6.0.0` [https://github.com/dotkernel/api/pull/522](https://github.com/dotkernel/api/pull/522)

### Optional updates

These changes cover development tooling, CI and documentation.
Skipping them does not affect how the API runs.

* Bump `dotkernel/dot-maker` to `^2.0.0` and add a Composer `conflict` on versions below `2.0` [https://github.com/dotkernel/api/pull/494](https://github.com/dotkernel/api/pull/494)
* Update PostgreSQL host configuration in `config/autoload/local.php.dist` to `127.0.0.1` [https://github.com/dotkernel/api/pull/465](https://github.com/dotkernel/api/pull/465)
* Add Bruno collection, update readme [https://github.com/dotkernel/api/pull/516](https://github.com/dotkernel/api/pull/516)
* Update API version in README example [https://github.com/dotkernel/api/pull/473](https://github.com/dotkernel/api/pull/473)
* Configure Renovate [https://github.com/dotkernel/api/pull/515](https://github.com/dotkernel/api/pull/515)
* Update branch for Qodana workflow to 7.0 [https://github.com/dotkernel/api/pull/491](https://github.com/dotkernel/api/pull/491)
* Update Qodana action version to v2025.3 [https://github.com/dotkernel/api/pull/500](https://github.com/dotkernel/api/pull/500)
* Update `JetBrains/qodana-action` to v2026 [https://github.com/dotkernel/api/pull/523](https://github.com/dotkernel/api/pull/523)
* Update `JetBrains/qodana-action` to v2026.1.3 [https://github.com/dotkernel/api/pull/527](https://github.com/dotkernel/api/pull/527)
* Update `actions/checkout` to v6 [https://github.com/dotkernel/api/pull/518](https://github.com/dotkernel/api/pull/518)
* Update `actions/checkout` to v7 [https://github.com/dotkernel/api/pull/528](https://github.com/dotkernel/api/pull/528)
* Update `actions/cache` to v5 [https://github.com/dotkernel/api/pull/517](https://github.com/dotkernel/api/pull/517)
* Update `actions/cache` to v6 [https://github.com/dotkernel/api/pull/530](https://github.com/dotkernel/api/pull/530)
* Update `codecov/codecov-action` to v6 [https://github.com/dotkernel/api/pull/520](https://github.com/dotkernel/api/pull/520)
* Update `codecov/codecov-action` to v7 [https://github.com/dotkernel/api/pull/526](https://github.com/dotkernel/api/pull/526)

## FAQ

**Q: Is there an automated upgrade from 7.0 to 7.1?**

A: No.
You implement each listed change manually in your own project.
See [Upgrades](upgrading.md) for the recommended procedure.

**Q: Which PHP versions does 7.1 support?**

A: PHP 8.3, 8.4 and 8.5.
PHP 8.2 is no longer supported; raise the `php` constraint in your `composer.json` before updating.

**Q: Do I need a database migration?**

A: Yes, if your project has the `AdminLogin` entity from 7.0.
Pull request 513 removes five columns and adds `isCrawler`.
Review it and generate a migration before touching production data.

**Q: Where did `mail.global.php` go?**

A: Pull request 499 removes it, and pull request 511 makes the post-install script copy the `dot-mail` distribution file to `config/autoload/mail.local.php` (and `mail.local.php.dist`).
The script skips files that already exist, so move your mail settings into `mail.local.php` yourself.

**Q: Do I have to regenerate my OAuth2 keys?**

A: No.
The new `bin/generate-oauth2-keys.php` from pull request 506 only generates keys when `data/oauth/encryption.key`, `private.key` or `public.key` is missing.
Test your authentication flow after upgrading to `mezzio-authentication-oauth2` 3.x (pull request 511).

**Q: Do I have to apply the optional updates?**

A: No.
They only concern tooling, CI and documentation.

**Q: Where do I find the complete list of changes?**

A: In the [7.1.0 release notes](https://github.com/dotkernel/api/releases/tag/7.1.0) and the project [CHANGELOG.md](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md).
