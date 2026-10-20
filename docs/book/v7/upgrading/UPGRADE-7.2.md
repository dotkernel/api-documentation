# Upgrading from 7.1 to 7.2

## Summary

The changes you need to port into your project when moving from Dotkernel API 7.1 to 7.2, each linked to the pull request that introduced it.
The headline items are the new `composer openapi` generator with its configuration file, the explicit `operationId` on every operation, and the changed exception status codes.

## Details

> You can find the release notes in [7.1.1](https://github.com/dotkernel/api/releases/tag/7.1.1), the [7.1.0...7.1.1 compare view](https://github.com/dotkernel/api/compare/7.1.0...7.1.1) and the commits on the [7.0 branch](https://github.com/dotkernel/api/commits/7.0).
> The project `CHANGELOG.md` does not list the 7.2 pull requests, and pull requests 565, 566 and 567 are not in any release notes, so they are listed here from their code changes.

### Important updates

These changes affect your Composer scripts and dependencies, configuration, the published OpenAPI document, response behavior or error behavior.

* Add `bin/generate-openapi.php`, the `openapi` Composer script (`php bin/generate-openapi.php`) and `config/autoload/openapi.global.php`: the OpenAPI document root (`info`, `servers`, security schemes, tags, external docs) now comes from configuration instead of attributes, and the server URL is taken from `application.url`. Copy the config file, add the script to your `composer.json`, add the `openapi` key with `server_description` to `config/autoload/local.php`, and add `public/openapi.yaml` to `.gitignore`. The generated document also defines a shared `DateTimeObject` schema [https://github.com/dotkernel/api/pull/560](https://github.com/dotkernel/api/pull/560)
* Move the OpenAPI root attributes (`Info`, `Server`, security schemes, external docs) out of `src/App/src/OpenAPI.php`: pull request 551 wraps them in `OA\OpenApi`, and pull request 560 then removes them in favor of the configuration file. Apply both together and remove the attributes from your copy of the file [https://github.com/dotkernel/api/pull/551](https://github.com/dotkernel/api/pull/551)
* Add an explicit `operationId` to every operation in the `OpenAPI.php` files, add a test that fails on a missing or duplicate id (`test/Unit/App/OpenApi/OperationIdTest.php`) and require `zircote/swagger-php` `^6.4.0`: the ids in the generated document change from hashes to readable names, for example `getAdminCollection` and `postUserResource`. Clients generated from the document must be regenerated [https://github.com/dotkernel/api/pull/566](https://github.com/dotkernel/api/pull/566)
* Change exception status codes: `RuntimeException` and `SunsetException` return `500 Internal Server Error` instead of `400` and `401`, and `ErrorReportService` throws `ForbiddenException` (`403`) instead of `RuntimeException` when error reporting is not enabled. Update API clients that match on the old status codes [https://github.com/dotkernel/api/pull/567](https://github.com/dotkernel/api/pull/567)
* Remove the `ResourceDeprecation` attribute from `GetIndexResourceHandler`: `GET /` no longer returns the `Sunset` and `Link` headers [https://github.com/dotkernel/api/pull/565](https://github.com/dotkernel/api/pull/565)

### Optional updates

These changes cover documentation fixes, type-only cleanups, development tooling and CI.
Skipping them does not affect how the API runs.

* Document the recover-account endpoint at its registered path in `src/User/src/OpenAPI.php`: `/user/account/recover` instead of `/account/recover-identity`. The route itself is unchanged [https://github.com/dotkernel/api/pull/550](https://github.com/dotkernel/api/pull/550)
* Add class constant type declarations and the `SlevomatCodingStandard.TypeHints.ClassConstantTypeHint` rule to `phpcs.xml`. It only matters if a subclass of the affected classes overrides one of those constants [https://github.com/dotkernel/api/pull/537](https://github.com/dotkernel/api/pull/537)
* Add `boundwize/structarmed` as a dev dependency with a `structarmed.php` architecture file, and make closures `static` [https://github.com/dotkernel/api/pull/553](https://github.com/dotkernel/api/pull/553)
* Update `boundwize/structarmed` to `^0.18.0` [https://github.com/dotkernel/api/pull/554](https://github.com/dotkernel/api/pull/554)
* Update `boundwize/structarmed` to `^0.19.0` [https://github.com/dotkernel/api/pull/562](https://github.com/dotkernel/api/pull/562)
* Clarify in `config/autoload/cors.local.php.dist` how to restrict the allowed origins [https://github.com/dotkernel/api/pull/536](https://github.com/dotkernel/api/pull/536)
* Update the README: password warning for the fixtures [https://github.com/dotkernel/api/pull/540](https://github.com/dotkernel/api/pull/540)
* Update the README to reflect the application purpose [https://github.com/dotkernel/api/pull/541](https://github.com/dotkernel/api/pull/541)
* Update `JetBrains/qodana-action` to v2026.2.0 [https://github.com/dotkernel/api/pull/532](https://github.com/dotkernel/api/pull/532)
* Update `JetBrains/qodana-action` to v2026.2.1 [https://github.com/dotkernel/api/pull/542](https://github.com/dotkernel/api/pull/542)
* Update `JetBrains/qodana-action` to v2026.2.2 [https://github.com/dotkernel/api/pull/557](https://github.com/dotkernel/api/pull/557)

## FAQ

**Q: Is there an automated upgrade from 7.1 to 7.2?**

A: No.
You implement each listed change manually in your own project.
See [Upgrades](upgrading.md) for the recommended procedure.

**Q: Which PHP versions does 7.2 support?**

A: The same as 7.1: PHP 8.3, 8.4 and 8.5.
No pull request in this release changes the `php` constraint in `composer.json`.

**Q: Do I need a database migration?**

A: No.
None of the pull requests in this release changes an entity, an enum or a migration.

**Q: Where did the OpenAPI root attributes go?**

A: Pull request 551 wraps them in `OA\OpenApi`, and pull request 560 removes them from `src/App/src/OpenAPI.php`.
The document root is now assembled by `bin/generate-openapi.php` from `config/autoload/openapi.global.php` and the `application.url` key, so run `composer openapi` to generate the document.

**Q: Will my API clients notice a change?**

A: Yes, in four places.
`RuntimeException` and `SunsetException` now return `500` instead of `400` and `401`, and a disabled error-report endpoint now returns `403` (pull request 567).
`GET /` no longer returns `Sunset` and `Link` headers (pull request 565).
The `operationId` values in the generated OpenAPI document change (pull request 566).
No route or request format changes.

**Q: Do I have to apply the optional updates?**

A: No.
They only concern documentation, type declarations, development tooling and CI.

**Q: Where do I find the complete list of changes?**

A: In the [7.1.1 release notes](https://github.com/dotkernel/api/releases/tag/7.1.1) and the [commits on the 7.0 branch](https://github.com/dotkernel/api/commits/7.0).
The project `CHANGELOG.md` does not list these pull requests.
