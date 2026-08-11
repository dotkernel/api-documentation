# Upgrading from 6.x to 7.0

## Summary

The changes you need to port into your project when moving from Dotkernel API 6.x to 7.0, each linked to the pull request that introduced it.
The headline items are native UUIDs in the database, PostgreSQL support, and the removal of the `MethodDeprecation` implementation.

## Details

> You can find a complete list in [Changelog](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md)

* Use native UUIDs in database via `ramsey/uuid` [https://github.com/dotkernel/api/pull/456](https://github.com/dotkernel/api/pull/456)
* updated readme, oss [https://github.com/dotkernel/api/pull/461](https://github.com/dotkernel/api/pull/461)
* PostgreSQL implementation [https://github.com/dotkernel/api/pull/462](https://github.com/dotkernel/api/pull/462)
* Remove `MethodDeprecation` implementation [https://github.com/dotkernel/api/pull/470](https://github.com/dotkernel/api/pull/470)
* Clarify instructions regarding multiple connections in `config/autoload/local.php.dist` [https://github.com/dotkernel/api/pull/472](https://github.com/dotkernel/api/pull/472)

## FAQ

**Q: Is there an automated upgrade from 6.x to 7.0?**

A: No. You implement each listed change manually in your own project.
See [Upgrades](upgrading.md) for the recommended procedure.

**Q: What does the switch to native UUIDs mean for my database?**

A: Identifiers are stored using the database's own UUID handling via `ramsey/uuid` rather than a generic column type, so existing tables need a migration.
Review pull request 456 before touching production data.

**Q: Do I have to move to PostgreSQL in 7.0?**

A: No. PostgreSQL is now supported in addition to MariaDB; either is a valid choice.

**Q: `MethodDeprecation` was removed — how do I deprecate an endpoint now?**

A: Use the deprecation approach described in [API evolution](../tutorials/api-evolution.md).

**Q: Where do I find the complete list of changes?**

A: In the project [CHANGELOG.md](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md).
