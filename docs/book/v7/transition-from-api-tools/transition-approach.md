# Transition approach  [WIP]

## Summary

How to think about moving an api-tools project onto Dotkernel API.
Because the two are not equivalent, the transition means recreating functionality — either by rebuilding all endpoints and entities one-to-one, or by running a new Dotkernel API platform alongside the old one until api-tools is retired.

## Details

Dotkernel API is not a one-to-one replacement of api-tools (former Apigility), but is only a potential solution to migrate to.

Functionalities, components and architecture are different.

See the [Comparison between Dotkernel APi and api-tools](https://docs.dotkernel.org/api-documentation/v4/transition-from-api-tools/api-tools-vs-dotkernel-api/).

## Business cases

There are at least two approaches for this transition:

- Clone 1:1 and recreate all endpoints and entities
- Build a new version of the current API using Dotkernel API and keep it running as separate platforms until the sunset of the current version of api-tools

## FAQ

**Q: Which of the two approaches should I choose?**

A: A one-to-one clone suits small APIs with a stable contract and few consumers.
Running both platforms in parallel suits larger APIs, because it lets you migrate consumers gradually instead of coordinating a single cutover.

**Q: Can I reuse my api-tools code in Dotkernel API?**

A: Business logic and validation rules usually transfer with adjustment, but controllers, routing and module configuration do not, since Dotkernel API uses a middleware architecture.
See [Laminas API Tools compared to Dotkernel API](api-tools-vs-dotkernel-api.md).

**Q: What should I do before starting the transition?**

A: Inventory the existing system first — database, authentication, modules, routes and custom code.
The [Discovery phase](discovery-phase.md) page lists the questions to answer.

**Q: Can I keep my existing database?**

A: Often yes, but Dotkernel API uses Doctrine ORM rather than laminas-db, so you will need to map your existing schema to entities.
See [Doctrine ORM](../installation/doctrine-orm.md).

**Q: Can the two platforms share authentication while running in parallel?**

A: Only if both issue and accept the same tokens.
Dotkernel API uses OAuth2, so an api-tools installation using HTTP Basic or Digest cannot share sessions with it directly.
