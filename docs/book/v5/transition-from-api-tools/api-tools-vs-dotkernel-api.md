# Laminas API Tools compared to Dotkernel API

|                     | API Tools (formerly Apigility)                 | Dotkernel API                                                                         |
|---------------------|------------------------------------------------|---------------------------------------------------------------------------------------|
| URL                 | [api-tools](https://api-tools.getlaminas.org/) | [Dotkernel API](https://www.dotkernel.org)                                            |
| First Release       | 2012                                           | 2018                                                                                  |
| PHP Version         | <= 8.2                                         | >= 8.1                                                                                |
| Architecture        | MVC, Event Driven                              | Middleware                                                                            |
| OSS Lifecycle       | Archived                                       | ![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/api?style=flat&label=) |
| Style               | REST, RPC                                      | REST                                                                                  |
| Versioning          | Yes                                            | [Deprecations *](https://docs.dotkernel.org/api-documentation/v5/tutorials/api-evolution/)|
| Documentation       | Swagger (Automated)                            | Postman (Manual) *                                                                    |
| Content-Negotiation | Custom                                         | Custom                                                                                |
| License             | BSD-3                                          | MIT                                                                                   |
| Default DB Layer    | laminas-db                                     | doctrine-orm                                                                          |
| Authorization       | ACL                                            | RBAC-guard                                                                            |
| Authentication      | HTTP Basic/Digest   OAuth2.0                   | OAuth2.0                                                                              |
| CI/CD               | Yes                                            | Yes                                                                                   |
| Unit Tests          | Yes                                            | Yes                                                                                   |
| Endpoint Generator  | Yes                                            | Under development                                                                     |
| PSR                 | PSR-7                                          | PSR-7, PSR-15                                                                         |

## Note

> * Versioning is replaced by Deprecations, using evolution strategy
> * Version 5 ([Roadmap](https://github.com/orgs/dotkernel/projects/15/views/1)) will implement OpenAPi 3.0
