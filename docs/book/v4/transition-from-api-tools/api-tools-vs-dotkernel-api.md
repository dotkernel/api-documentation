## Comparison between API Tools and DotKernel API

|                     | API Tools (Apigility)                          | DotKernel API                                                            |
|---------------------|------------------------------------------------|--------------------------------------------------------------------------|
| URL                 | [api-tools](https://api-tools.getlaminas.org/) | [Dotkernel API](https://www.dotkernel.org)                               |
| First Release       | 2012                                           | 2018                                                                     |
| PHP Version         | <= 8.2                                         | >= 8.1                                                                   |
| Architecture        | MVC, Event Driven                              | Middleware                                                               |
| OSS Lifecycle       | Archived                                       | ![](https://img.shields.io/osslifecycle/dotkernel/api?style=flat&label=) |
| Style               | REST, RPC                                      | REST                                                                     |
| Versioning          | Yes                                            | No *                                                                     |
| Documentation       | Swagger (Automated)                            | Postman (Manual)                                                         |
| Content-Negotiation | Custom                                         | Hardcoded (hal+json, json)                                               |
| License             | BSD-3                                          | MIT                                                                      |
| Default DB Layer    | laminas-db                                     | doctrine-orm                                                             |
| Authorization       | ACL                                            | RBAC-guard                                                               |
| Authentication      | HTTP Basic/Digest <br/> OAuth2.0               | OAuth2.0                                                                 |
| CI/CD               | Yes                                            | Yes                                                                      |
| Unit Tests          | Yes                                            | Yes                                                                      |
| Endpoint Generator  | Yes                                            | Under development                                                        |
| PSR                 | PSR-7                                          | PSR-7, PSR-15                                                            |


### Note
> * Versioning is not planned at all. [Quote from Roy T. Fielding](https://twitter.com/fielding/status/376835835670167552)