# Upgrading from 7.1 to 7.2

## Summary

The changes you need to port into your project when moving from Dotkernel API 7.1 to 7.2, each linked to the pull request that introduced it.
The headline items are the new `composer openapi` generator with its configuration file, the explicit `operationId` on every operation, the changed exception status codes, and the `410 Gone` response for resources past their sunset date.

## Details

> Upgrading from 7.1 to 7.2 covers two releases: [7.1.1](https://github.com/dotkernel/api/releases/tag/7.1.1) and [7.2.0](https://github.com/dotkernel/api/releases/tag/7.2.0).
> You can find the changes in their release notes, in the compare views [7.1.0...7.1.1](https://github.com/dotkernel/api/compare/7.1.0...7.1.1) and [7.1.1...7.2.0](https://github.com/dotkernel/api/compare/7.1.1...7.2.0), and in the project `CHANGELOG.md`.

### Important updates

These changes affect your Composer scripts and dependencies, configuration, the published OpenAPI document, response behavior or error behavior.

* [https://github.com/dotkernel/api/pull/560](https://github.com/dotkernel/api/pull/560)
    * Add the `composer openapi` generator: the OpenAPI document root (`info`, `servers`, security schemes, tags, external docs) now comes from configuration instead of attributes, and the first server URL is taken from `application.url`.
    * Copy `bin/generate-openapi.php`, the `src/App/src/Helper/OpenApiServers.php` helper it imports, and `config/autoload/openapi.global.php`.
    * Add the `openapi` script (`php bin/generate-openapi.php`) to your `composer.json` and require `zircote/swagger-php` `^6.4.0`, the first version that provides the `OpenApi\Builder` class the script uses (see pull request 566).
    * Add the `openapi` key with `server_description` to `config/autoload/local.php`, and add `public/openapi.yaml` to `.gitignore`.
    * Make sure `application.url` and `openapi.output_file` are set: the script exits with `1` without them.
    * Note that `output_file` defaults to `public/openapi.yaml`, which the web server serves, so point it elsewhere if the document must stay private.
    * Optionally configure `exclude_tags` (hide a module from the document), `servers` (additional servers), `generated_timezone` (the `info.x-generated` timestamp, or `null` to omit it) and `openapi_version`.
    * In the `OpenAPI.php` files of the `Admin` and `User` modules, replace the `created`, `updated` and `expires` properties with a `$ref` to the new shared `DateTimeObject` schema, which is defined in `src/App/src/OpenAPI.php`.
    * Update the `HomeMessage` example to `Dotkernel API version 7`.
* [https://github.com/dotkernel/api/pull/551](https://github.com/dotkernel/api/pull/551)
    * Move the OpenAPI root attributes (`Info`, `Server`, security schemes, external docs) out of `src/App/src/OpenAPI.php`: pull request 551 wraps them in `OA\OpenApi`, and pull request 560 then removes them in favor of the configuration file.
    * Apply both together and remove the attributes from your copy of the file.
* [https://github.com/dotkernel/api/pull/566](https://github.com/dotkernel/api/pull/566)
    * Every operation gets an explicit `operationId`: the ids in the generated document change from hashes to readable names, for example `getAdminCollection` and `postUserResource`.
    * Add an `operationId` to every operation in the `OpenAPI.php` files.
    * Add the test `test/Unit/App/OpenApi/OperationIdTest.php`, which fails on a missing or duplicate id.
    * Require `zircote/swagger-php` `^6.4.0`. Apply this together with pull request 560: its generator needs `OpenApi\Builder`, which first appears in `^6.4.0`, and the new test needs `OpenApi\Undefined`, which first appears in 6.3.1.
    * Regenerate any client that was generated from the document.
* [https://github.com/dotkernel/api/pull/567](https://github.com/dotkernel/api/pull/567)
    * `RuntimeException` and `SunsetException` return `500 Internal Server Error` instead of `400` and `401`, and `ErrorReportService` throws `ForbiddenException` (`403`) instead of `RuntimeException` when error reporting is not enabled.
    * `RuntimeException` is also thrown by the other `ErrorReportService` configuration checks, the template renderer, `HandlerService` and the handler factories, so those failures now return `500` as well, including `POST /error-report` when the endpoint is misconfigured.
    * `SunsetException` is thrown for an invalid `sunset` value in `ResourceDeprecation`.
    * Update API clients that match on the old status codes.
* [https://github.com/dotkernel/api/pull/565](https://github.com/dotkernel/api/pull/565)
    * Remove the `ResourceDeprecation` attribute from `GetIndexResourceHandler`, the only handler in the skeleton that used it: `GET /` no longer returns the `Sunset` and `Link` headers.
    * Handlers you annotated yourself keep their attribute; see pull request 570 for what happens once their sunset date passes.
* [https://github.com/dotkernel/api/pull/570](https://github.com/dotkernel/api/pull/570)
    * A resource whose `ResourceDeprecation` sunset date has been reached is no longer executed. Before, the handler still ran and only the `Sunset` header was added.
    * The request now throws `ExpiredException` and returns `410 Gone` with the detail `This resource is no longer available (sunset on <date>).`.
    * The problem-details `type` is the attribute's `link`, or `application.versioning.documentation_url` when the attribute has none.
    * Copy `src/App/src/Middleware/DeprecationMiddleware.php`.
    * Check every handler of yours that uses `#[ResourceDeprecation]` for a sunset date that is already in the past.

### Optional updates

These changes cover documentation fixes, type-only cleanups, development tooling and CI.
Skipping them does not affect how the API runs.

* [https://github.com/dotkernel/api/pull/550](https://github.com/dotkernel/api/pull/550)
    * Document the recover-account endpoint at its registered path in `src/User/src/OpenAPI.php`: `/user/account/recover` instead of `/account/recover-identity`.
    * The route itself is unchanged.
* [https://github.com/dotkernel/api/pull/537](https://github.com/dotkernel/api/pull/537)
    * Add class constant type declarations (for example in the input filters, the DBAL types, `Message`, `ConfigProvider`, `UserAvatarService`, `ContentNegotiationMiddleware` and `ErrorReportService`) and the `SlevomatCodingStandard.TypeHints.ClassConstantTypeHint` rule to `phpcs.xml`.
    * It only matters if a subclass of the affected classes overrides one of those constants.
* [https://github.com/dotkernel/api/pull/553](https://github.com/dotkernel/api/pull/553)
    * Add `boundwize/structarmed` as a dev dependency with a `structarmed.php` architecture file, and make closures `static`
* [https://github.com/dotkernel/api/pull/554](https://github.com/dotkernel/api/pull/554)
    * Update `boundwize/structarmed` to `^0.18.0`
* [https://github.com/dotkernel/api/pull/562](https://github.com/dotkernel/api/pull/562)
    * Update `boundwize/structarmed` to `^0.19.0`
* [https://github.com/dotkernel/api/pull/536](https://github.com/dotkernel/api/pull/536)
    * Clarify in `config/autoload/cors.local.php.dist` how to restrict the allowed origins
* [https://github.com/dotkernel/api/pull/540](https://github.com/dotkernel/api/pull/540)
    * Update the README: password warning for the fixtures
* [https://github.com/dotkernel/api/pull/541](https://github.com/dotkernel/api/pull/541)
    * Update the README to reflect the application purpose
* [https://github.com/dotkernel/api/pull/532](https://github.com/dotkernel/api/pull/532)
    * Update `JetBrains/qodana-action` to v2026.2.0
* [https://github.com/dotkernel/api/pull/542](https://github.com/dotkernel/api/pull/542)
    * Update `JetBrains/qodana-action` to v2026.2.1
* [https://github.com/dotkernel/api/pull/557](https://github.com/dotkernel/api/pull/557)
    * Update `JetBrains/qodana-action` to v2026.2.2

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

A: Yes, in five places.
`RuntimeException` and `SunsetException` now return `500` instead of `400` and `401`, and a disabled error-report endpoint now returns `403` (pull request 567).
`GET /` no longer returns `Sunset` and `Link` headers (pull request 565).
A resource whose `ResourceDeprecation` sunset date has been reached now returns `410 Gone` instead of running (pull request 570).
The `operationId` values in the generated OpenAPI document change (pull request 566).
The document is now served from `public/openapi.yaml` after `composer openapi`, unless you change `openapi.output_file` (pull request 560).
No route or request format changes.

**Q: Do I have to apply the optional updates?**

A: No.
They only concern documentation, type declarations, development tooling and CI.

**Q: Where do I find the complete list of changes?**

A: In the release notes of [7.1.1](https://github.com/dotkernel/api/releases/tag/7.1.1) and [7.2.0](https://github.com/dotkernel/api/releases/tag/7.2.0), the compare views [7.1.0...7.1.1](https://github.com/dotkernel/api/compare/7.1.0...7.1.1) and [7.1.1...7.2.0](https://github.com/dotkernel/api/compare/7.1.1...7.2.0), and the project `CHANGELOG.md`.
