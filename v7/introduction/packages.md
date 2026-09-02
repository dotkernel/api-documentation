# Packages

## Summary

The third-party and Dotkernel packages Dotkernel API depends on, with the version constraint and purpose of each.
They fall into a few groups: Doctrine for persistence, Laminas components for configuration, hydration, validation and the service container, Mezzio for the PSR-15 pipeline with OAuth2, RBAC, CORS, HAL and problem details, the `dotkernel/dot-*` libraries, and `zircote/swagger-php` for API documentation.

## Details

* `doctrine/dbal`:`^4.4` - Powerful PHP database abstraction layer (DBAL) with many features for database schema introspection and management.
* `doctrine/orm`:`^3.6` - Object-Relational-Mapper for PHP
* `dotkernel/dot-cache`:`^4.4` - Cache component extending symfony-cache
* `dotkernel/dot-cli`:`^3.10` - Component for creating console applications based on laminas-cli
* `dotkernel/dot-data-fixtures`:`^1.5` - Provides a CLI interface for listing & executing doctrine data fixtures
* `dotkernel/dot-dependency-injection`:`^1.3` - Dependency injection component using class attributes.
* `dotkernel/dot-errorhandler`:`^4.0` - Logging Error Handler for Middleware Applications
* `dotkernel/dot-mail`:`^5.4` - Mail component based on Symfony Mailer
* `dotkernel/dot-response-header`:`^3.6` - Middleware for setting custom response headers.
* `dotkernel/dot-router`:`^1.1` - Dotkernel component to build complex routes, based on `mezzio/mezzio-fastroute`
* `laminas/laminas-authentication`:`^2.19` - API for authentication and includes concrete authentication adapters for common use case scenarios
* `laminas/laminas-component-installer`:`^3.7` - Composer plugin for injecting modules and configuration providers into application configuration
* `laminas/laminas-config-aggregator`:`^1.19` - Lightweight library for collecting and merging configuration from different sources
* `laminas/laminas-hydrator`:`^4.18` - Serialize objects to arrays, and vice versa
* `laminas/laminas-inputfilter`:`^2.35` - Normalize and validate input sets from the web, APIs, the CLI, and more, including files
* `laminas/laminas-servicemanager`:`^3.24` - Factory-Driven Dependency Injection Container
* `laminas/laminas-stdlib`:`^3.21` - SPL extensions, array utilities, error handlers, and more
* `mezzio/mezzio`:`^3.27` - PSR-15 Middleware Microframework
* `mezzio/mezzio-authentication-oauth2`:`^2.14` - OAuth2 (server) authentication middleware for Mezzio and PSR-15 applications
* `mezzio/mezzio-authorization-acl`:`^1.13` - laminas-permissions-acl adapter for mezzio-authorization
* `mezzio/mezzio-authorization-rbac`:`^1.10` - mezzio authorization rbac adapter for laminas/laminas-permissions-rbac
* `mezzio/mezzio-cors`:`^1.16` - CORS component for Mezzio and other PSR-15 middleware runners
* `mezzio/mezzio-fastroute`:`^3.14` - FastRoute integration for Mezzio
* `mezzio/mezzio-hal`:`^2.13` - Hypertext Application Language implementation for PHP and PSR-15
* `mezzio/mezzio-helpers`:`^5.20` - Helper/Utility classes for Mezzio
* `mezzio/mezzio-problem-details`:`^1.19` - Problem Details for PSR-15 HTTP APIs addressing the RFC 7807 standard
* `ramsey/uuid`:`^4.9` - A PHP library for generating and working with universally unique identifiers (UUIDs).
* `ramsey/uuid-doctrine`:`^2.1` - Use ramsey/uuid as a Doctrine field type
* `roave/psr-container-doctrine`:`^5.2` || `^6.1` - Doctrine Factories for PSR-11 Containers
* `symfony/filesystem`:`^7.4` - Provides basic utilities for the filesystem
* `zircote/swagger-php`:`^5.8` - Generate interactive documentation for your RESTful API using PHP attributes (preferred) or PHPDoc annotations

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

**Q: What are the `dotkernel/dot-*` packages for?**

A: They are Dotkernel's own components — CLI, cache, mail, router, error handler, dependency injection and data fixtures — reused across Dotkernel projects rather than duplicated in each one.

**Q: Can I remove a package I don't use?**

A: Some can be removed, but many are wired into the default configuration and pipeline.
Remove the corresponding configuration and pipeline entries first, then verify the application and tests still run.

**Q: Why are both the ACL and RBAC authorization adapters present?**

A: RBAC is what Dotkernel API uses by default; the ACL adapter is available if your project needs access-control-list semantics instead.
