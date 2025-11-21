# Laminas API Tools compared to Dotkernel API

|                     | API Tools (formerly Apigility)                 | Dotkernel API                                                                                                                                          |
|---------------------|------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|
| URL                 | [api-tools](https://api-tools.getlaminas.org/) | [Dotkernel API](https://www.dotkernel.org)                                                                                                             |
| First Release       | 2012                                           | 2018                                                                                                                                                   |
| PHP Version         | <= 8.2                                         | >= 8.2                                                                                                                                                 |
| Architecture        | MVC, Event Driven                              | Middleware                                                                                                                                             |
| OSS Lifecycle       | Archived                                       | ![OSS Lifecycle](https://img.shields.io/osslifecycle?style=flat&label=&file_url=https%3A%2F%2Fgithub.com%2Fdotkernel%2Fapi%2Fblob%2F5.0%2FOSSMETADATA) |
| Style               | REST, RPC                                      | REST                                                                                                                                                   |
| Versioning          | Yes                                            | [Deprecations *](https://docs.dotkernel.org/api-documentation/v5/tutorials/api-evolution/)                                                             |
| Documentation       | Swagger (Automated)                            | OpenAPI (Swagger) / Postman (Manual) *                                                                                                                 |
| Content-Negotiation | Custom                                         | Custom                                                                                                                                                 |
| License             | BSD-3                                          | MIT                                                                                                                                                    |
| Default DB Layer    | laminas-db                                     | doctrine-orm                                                                                                                                           |
| Authorization       | ACL                                            | RBAC-guard                                                                                                                                             |
| Authentication      | HTTP Basic/Digest   OAuth2.0                   | OAuth2.0                                                                                                                                               |
| CI/CD               | Yes                                            | Yes                                                                                                                                                    |
| Unit Tests          | Yes                                            | Yes                                                                                                                                                    |
| Endpoint Generator  | Yes                                            | Under development                                                                                                                                      |
| PSR                 | PSR-7                                          | PSR-7, PSR-15                                                                                                                                          |

> \* Versioning was replaced by Deprecations, using evolution strategy
> \* Version 5 implemented OpenAPI 3.0
