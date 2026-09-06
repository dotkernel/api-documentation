# Packages

## Summary

The third-party and Dotkernel packages Dotkernel API depends on, with the version constraint and purpose of each, split into runtime requirements, packages that arrive as dependencies, and development-only tooling.
They cover Doctrine for persistence, Laminas components for configuration, hydration, validation and the service container, Mezzio for the PSR-15 pipeline with OAuth2, RBAC, CORS, HAL and problem details, the `dotkernel/dot-*` libraries, and `zircote/swagger-php` for API documentation.

## Details

The authoritative source is the `require` section of `composer.json`.
The constraints below mirror it exactly, with a short note on what each entry is for.

### Runtime requirements

Composer refuses to install the project unless all of these are satisfied:

* `php`:`~8.3.0 || ~8.4.0 || ~8.5.0` - PHP 8.3, 8.4 or 8.5; see [Server requirements](server-requirements.md)
* `ext-gd`:`*` - the GD extension; must be enabled on the server
* `ext-json`:`*` - the JSON extension; ships enabled and cannot be disabled on any supported PHP version
* `dotkernel/dot-cache`:`^4.3` - Cache component extending symfony-cache
* `dotkernel/dot-cli`:`^3.9.0` - Component for creating console applications based on laminas-cli
* `dotkernel/dot-data-fixtures`:`^1.4.0` - Provides a CLI interface for listing & executing doctrine data fixtures
* `dotkernel/dot-dependency-injection`:`^1.2` - Dependency injection component using class attributes
* `dotkernel/dot-errorhandler`:`^4.0.0` - Logging Error Handler for Middleware Applications
* `dotkernel/dot-mail`:`^5.3.0` - Mail component based on Symfony Mailer
* `dotkernel/dot-response-header`:`^3.5.0` - Middleware for setting custom response headers
* `dotkernel/dot-router`:`^1.0.5` - Dotkernel component to build complex routes, based on `mezzio/mezzio-fastroute`
* `laminas/laminas-authentication`:`^2.18` - API for authentication and includes concrete authentication adapters for common use case scenarios
* `laminas/laminas-component-installer`:`^3.5.0` - Composer plugin for injecting modules and configuration providers into application configuration
* `laminas/laminas-config-aggregator`:`^1.18.0` - Lightweight library for collecting and merging configuration from different sources
* `laminas/laminas-hydrator`:`^4.16.0` - Serialize objects to arrays, and vice versa
* `laminas/laminas-inputfilter`:`^2.31.0` - Normalize and validate input sets from the web, APIs, the CLI, and more, including files
* `laminas/laminas-stdlib`:`^3.20.0` - SPL extensions, array utilities, error handlers, and more
* `mezzio/mezzio`:`^3.20.1` - PSR-15 Middleware Microframework
* `mezzio/mezzio-authentication-oauth2`:`^3.0.1` - OAuth2 (server) authentication middleware for Mezzio and PSR-15 applications
* `mezzio/mezzio-authorization-acl`:`^1.11.0` - laminas-permissions-acl adapter for mezzio-authorization
* `mezzio/mezzio-authorization-rbac`:`^1.8.0` - mezzio authorization rbac adapter for laminas/laminas-permissions-rbac
* `mezzio/mezzio-cors`:`^1.13.0` - CORS component for Mezzio and other PSR-15 middleware runners
* `mezzio/mezzio-fastroute`:`^3.12.0` - FastRoute integration for Mezzio
* `mezzio/mezzio-hal`:`^2.10.1` - Hypertext Application Language implementation for PHP and PSR-15
* `mezzio/mezzio-helpers`:`^5.18` - Helper/Utility classes for Mezzio
* `mezzio/mezzio-problem-details`:`^1.15.0` - Problem Details for PSR-15 HTTP APIs, addressing the RFC 9457 standard (formerly RFC 7807)
* `ramsey/uuid`:`^4.5.0` - A PHP library for generating and working with universally unique identifiers (UUIDs). `4.5.0` is the release that introduced `Uuid::uuid7()`, which this project uses for every entity identifier
* `ramsey/uuid-doctrine`:`^2.1.0` - Use ramsey/uuid as a Doctrine field type
* `roave/psr-container-doctrine`:`^5.2.2 || ^6.0.0` - Doctrine Factories for PSR-11 Containers
* `symfony/filesystem`:`^7.2.0` - Provides basic utilities for the filesystem
* `symfony/var-exporter`:`^6.4 || ^7.4` - Exports serializable PHP data structures as plain PHP code; required to keep Doctrine LazyGhost proxy support
* `zircote/swagger-php`:`^6.0.0` - Generate interactive documentation for your RESTful API using PHP attributes (preferred) or PHPDoc annotations

### Installed as dependencies

These are used directly by the application but are **not** declared in `composer.json`.
They are resolved transitively through the packages listed above, so a normal install provides them — but the project pins no version of its own for them.

* `doctrine/orm` - Object-Relational-Mapper for PHP; the persistence layer for every entity and repository
* `doctrine/dbal` - Database abstraction and schema layer that the ORM is built on; also where the migrations and the custom `UUID` type operate
* `laminas/laminas-servicemanager` - Factory-driven PSR-11 container; `config/container.php` instantiates it directly

To see which versions you actually have, run `composer show doctrine/orm` (or `composer show --tree`) rather than relying on a constraint, since there is none to read.

### Development requirements

Declared under `require-dev`, installed unless you pass `--no-dev`:

* `dotkernel/dot-maker`:`^2.0.0` - Generates modules, handlers, services and input filters; see [Creating a book module using DotMaker](../tutorials/create-book-module-via-dot-maker.md)
* `laminas/laminas-coding-standard`:`^3.0.1` - Coding standard ruleset used by `composer cs-check` and `composer cs-fix`
* `laminas/laminas-development-mode`:`^3.13.0` - Toggles development mode; see [Composer](../installation/composer.md)
* `phpstan/phpstan`:`^2.1.11` - Static analysis, run by `composer static-analysis`
* `phpstan/phpstan-doctrine`:`^2.0.2` - PHPStan extension that understands Doctrine
* `phpstan/phpstan-phpunit`:`^2.0.6` - PHPStan extension that understands PHPUnit
* `phpunit/phpunit`:`^12.5.23` - Test framework for the unit and functional suites
* `roave/security-advisories`:`dev-latest` - Blocks installation of packages with known vulnerabilities
* `symfony/var-dumper`:`^7.2.3` - Debug output helper

## FAQ

**Q: Is this list authoritative?**

A: The authoritative source is the `require` section of the project's `composer.json`.
This page mirrors it with a short explanation of each entry.

**Q: Which package provides the framework itself?**

A: `mezzio/mezzio`, a PSR-15 middleware microframework.
See [PSRs](psr.md).

**Q: Which packages handle authentication and authorization?**

A: `mezzio/mezzio-authentication-oauth2` for OAuth2 authentication, and `mezzio/mezzio-authorization-rbac` for role-based authorization.
See [Authentication](../core-features/authentication.md) and [Authorization](../core-features/authorization.md).

**Q: Why are both `doctrine/dbal` and `doctrine/orm` listed?**

A: DBAL is the database abstraction and schema layer; ORM maps entities on top of it.
The ORM requires DBAL underneath.
See [Doctrine ORM](../installation/doctrine-orm.md).

**Q: Why do `doctrine/orm`, `doctrine/dbal` and `laminas/laminas-servicemanager` have no version constraint?**

A: Because `composer.json` does not declare them.
The application uses all three directly — Doctrine throughout the entities, repositories and migrations, and the service manager in `config/container.php` — but they are installed transitively as dependencies of the declared packages.
Run `composer show <package>` to see the version you have.

**Q: Why does `ramsey/uuid` require at least `4.5.0` specifically?**

A: `4.5.0` is the release that added `Uuid::uuid7()`.
Every entity identifier is a UUIDv7 generated in PHP, so an older release would not provide the method.
See [Server requirements](server-requirements.md) for the matching MariaDB requirement.

**Q: Are the development packages needed in production?**

A: No.
Deploy with `composer install --no-dev` and none of the `require-dev` entries are installed.

**Q: What are the `dotkernel/dot-*` packages for?**

A: They are Dotkernel's own components — CLI, cache, mail, router, error handler, dependency injection and data fixtures — reused across Dotkernel projects rather than duplicated in each one.

**Q: Can I remove a package I don't use?**

A: Some can be removed, but many are wired into the default configuration and pipeline.
Remove the corresponding configuration and pipeline entries first, then verify the application and tests still run.

**Q: Why are both the ACL and RBAC authorization adapters present?**

A: RBAC is what Dotkernel API uses by default; the ACL adapter is available if your project needs access-control-list semantics instead.
