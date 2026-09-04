# Discovery phase for a current system built using API Tools [WIP]

## Summary

A checklist of what to inventory in an existing api-tools system before migrating it: the database and its access layer, the authentication and authorization schemes, each module's configuration, routes, response formats and validation rules, and any custom code that was written by hand rather than generated.

## Details

To transition a system built using api-tools to Dotkernel API, we need to analyze the core components of it.

## Database

- is there a database in the current API?
- which is the connection to a database
- which library is used for database interaction (laminas-db, doctrine 2, eloquent, or else)?

> Dotkernel API version 7 is tested only with MariaDB version 11.4 LTS, 11.8 LTS, 12.3 LTS, and PostgreSQL version 13 and above.
> MariaDB 11.4 is the minimum supported version — see [Server requirements](../introduction/server-requirements.md).

## Authentication and Authorization

- how is authentication done? (basic, digest, oauth2, etc.)
- how is authorization done? (acl, rbac)

## Modules

- analyze configuration files of the modules (what needs to be configured to use a module)
- analyze routes (which are the routes, protection rules, which one needs auth, etc.)
- analyze a response format (content negotiation and validation, which ones are JSON, hal, views, etc.)
- analyze input field validations

## Custom functionalities

Analyze the custom code (code that cannot be generated through Admin UI and requires manual implementation)

For instance:

- caching
- events
- services
- extra installed packages and libraries
- jobs and queues
- third-parties
- tests

## FAQ

**Q: Why is a discovery phase necessary?**

A: Because api-tools generated much of its behaviour from configuration, the working system contains decisions that are not obvious from the code alone.
Documenting them first prevents discovering missing requirements mid-migration.

**Q: What if the current API uses a database library other than Doctrine?**

A: Plan on mapping the schema to Doctrine entities.
Dotkernel API's default data layer is Doctrine ORM, so laminas-db or Eloquent code does not carry over.
See [Doctrine ORM](../installation/doctrine-orm.md).

**Q: Which database versions can I target?**

A: Dotkernel API version 7 is tested with MariaDB 11.4 LTS, 11.8 LTS and 12.3 LTS, and with PostgreSQL 13 and above.
MariaDB 11.4 is the minimum, because earlier releases byte-swap `UUID` values and lose the ordering of the UUIDv7 identifiers the project generates.
See [Server requirements](../introduction/server-requirements.md).

**Q: My api-tools API uses HTTP Basic authentication. What is the equivalent?**

A: Dotkernel API authenticates with OAuth2.
Basic and Digest schemes have no direct equivalent, so consumers need to be updated.
See [Authentication](../core-features/authentication.md).

**Q: How do I document existing response formats?**

A: Record which endpoints return JSON, HAL or other representations, then map them onto Dotkernel API's content negotiation.
See [Content validation](../core-features/content-validation.md).

**Q: What counts as "custom functionality"?**

A: Anything that could not be produced by the api-tools Admin UI — caching, event listeners, services, queues, third-party integrations and tests.
These always need manual reimplementation.
