# Laminas API Tools compared to Dotkernel API

## Summary

A side-by-side comparison of Laminas API Tools (formerly Apigility) and Dotkernel API across architecture, PHP support, database layer, authentication, authorization, documentation and tooling.
API Tools is archived and MVC/event-driven; Dotkernel API is actively maintained and middleware-based.

## Details

|                     | API Tools (formerly Apigility)                 | Dotkernel API                                                                                                                                          |
|---------------------|------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| URL                 | [api-tools](https://api-tools.getlaminas.org/) | [Dotkernel API](https://www.dotkernel.org)                                                                                                             |
| First Release       | 2012                                           | 2018                                                                                                                                                   |
| PHP Version         | <= 8.2                                         | 8.3, 8.4, 8.5                                                                                                                                          |
| Architecture        | MVC, Event Driven                              | Middleware                                                                                                                                             |
| OSS Lifecycle       | Archived                                       | ![OSS Lifecycle](https://img.shields.io/osslifecycle?style=flat&label=&file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fapi%2Fblob%2F7.0%2FOSSMETADATA) |
| Style               | REST, RPC                                      | REST                                                                                                                                                   |
| Versioning          | Yes                                            | [Deprecations](https://docs.dotkernel.org/api-documentation/v7/tutorials/api-evolution/)                                                               |
| Documentation       | Swagger (Automated)                            | OpenAPI (Swagger) / Bruno (Manual)                                                                                                                     |
| Content-Negotiation | Custom                                         | Custom                                                                                                                                                 |
| License             | BSD-3                                          | MIT                                                                                                                                                    |
| Default DB Layer    | laminas-db                                     | doctrine-orm                                                                                                                                           |
| Authorization       | ACL                                            | RBAC-guard                                                                                                                                             |
| Authentication      | HTTP Basic/Digest   OAuth2.0                   | OAuth2.0                                                                                                                                               |
| CI/CD               | Yes                                            | Yes                                                                                                                                                    |
| Unit Tests          | Yes                                            | Yes                                                                                                                                                    |
| Code Generator      | Yes                                            | [dotkernel/dot-maker](https://www.dotkernel.com/headless-platform/dotmaker-generate-common-code-in-dotkernel/)                                         |
| PSR                 | PSR-7                                          | PSR-7, PSR-15                                                                                                                                          |

## FAQ

**Q: Is Dotkernel API a drop-in replacement for API Tools?**

A: No. The two projects differ in architecture, components and functionality, so a transition is a rewrite rather than a swap.
See [Transition approach](transition-approach.md).

**Q: What is the biggest architectural difference?**

A: API Tools is MVC and event-driven; Dotkernel API is a PSR-15 middleware pipeline.
Request handling, routing and extension points all work differently as a result.

**Q: Does Dotkernel API support RPC-style endpoints?**

A: No. Dotkernel API is REST only, while API Tools supported both REST and RPC.

**Q: How is versioning handled without API Tools' version support?**

A: Dotkernel API uses deprecation rather than parallel versioned namespaces.
See [API evolution](../tutorials/api-evolution.md).

**Q: Is there a code generator like API Tools' Admin UI?**

A: There is no web UI, but [dotkernel/dot-maker](https://www.dotkernel.com/headless-platform/dotmaker-generate-common-code-in-dotkernel/) generates common code from the command line.
See [Creating a book module using DotMaker](../tutorials/create-book-module-via-dot-maker.md).

**Q: Why should I move off API Tools at all?**

A: API Tools is archived, so it receives no further development.
Dotkernel API targets current PHP versions and is actively maintained.
