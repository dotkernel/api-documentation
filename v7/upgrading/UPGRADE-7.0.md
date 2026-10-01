# Upgrading from 6.x to 7.0

## Summary

The changes you need to port into your project when moving from Dotkernel API 6.x to 7.0, each linked to the pull request that introduced it.
The headline items are native UUIDs in the database, PostgreSQL support with renamed database connection keys, and the removal of the `MethodDeprecation` implementation.
Version 7.0 does not change the PHP version or any Composer dependency constraint.

## Details

> You can find the release notes in [7.0.0](https://github.com/dotkernel/api/releases/tag/7.0.0) and a complete list in [Changelog](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md)

### Important updates

These changes affect your database schema, configuration, entities or runtime behavior.

* Use native UUIDs in database via `ramsey/uuid`: entities get their identifier from the new `UuidIdentifierTrait` and a `UuidType` that declares the SQL type `UUID`. `getUuid()` becomes `getId()` and the `uuid` key becomes `id` in entities, repositories, services, input filters and OpenAPI. A schema migration is required [https://github.com/dotkernel/api/pull/456](https://github.com/dotkernel/api/pull/456)
* PostgreSQL implementation: in `config/autoload/local.php.dist` the `default` connection is renamed `mariadb`, a `postgresql` connection is added, and `charset` and `collate` are replaced by `collation`. `AbstractEnumType` is reworked, `MigrationsMigratedSubscriber` is added and `config/cli-config.php` is updated [https://github.com/dotkernel/api/pull/462](https://github.com/dotkernel/api/pull/462)
* Remove `MethodDeprecation` implementation: the attribute and its tests are deleted, and `DeprecationMiddleware` and the error-report handler no longer use it [https://github.com/dotkernel/api/pull/470](https://github.com/dotkernel/api/pull/470)

### Optional updates

These changes cover documentation and comments.
Skipping them does not affect how the API runs.

* Clarify instructions regarding multiple connections in `config/autoload/local.php.dist` [https://github.com/dotkernel/api/pull/472](https://github.com/dotkernel/api/pull/472)
* Update readme and security documents [https://github.com/dotkernel/api/pull/461](https://github.com/dotkernel/api/pull/461)

## FAQ

**Q: Is there an automated upgrade from 6.x to 7.0?**

A: No.
You implement each listed change manually in your own project.
See [Upgrades](upgrading.md) for the recommended procedure.

**Q: Do I need to change my PHP version or dependencies?**

A: No.
The PHP constraint and the Composer dependencies are the same in 6.1.0 and 7.0.0.

**Q: Which code or configuration do I need to rename?**

A: Rename `getUuid()` to `getId()` and the `uuid` key to `id` wherever your own code uses them.
In your `config/autoload/local.php`, rename the `default` database connection to `mariadb` and replace `charset` and `collate` with `collation`.
Compare your file with `local.php.dist` from the 7.0 branch.

**Q: What does the switch to native UUIDs mean for my database?**

A: The identifier column type changes from `uuid_binary` to the database's `UUID` type, through a `UuidType` that extends the `ramsey/uuid-doctrine` type.
The release does not ship a migration file, so existing tables need a migration that you write for your own schema.
Review pull request 456 before touching production data.

**Q: Do I have to move to PostgreSQL in 7.0?**

A: No.
PostgreSQL is now supported in addition to MariaDB; either is a valid choice, and MariaDB remains the default connection.

**Q: `MethodDeprecation` was removed — how do I deprecate an endpoint now?**

A: Use the deprecation approach described in [API evolution](../tutorials/api-evolution.md).

**Q: Do I have to apply the optional updates?**

A: No.
They only concern documentation and comments.

**Q: Where do I find the complete list of changes?**

A: In the [7.0.0 release notes](https://github.com/dotkernel/api/releases/tag/7.0.0) and the project [CHANGELOG.md](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md).
