# Discovery phase for a current system built using API Tools [WIP]

In order to transition a system built using api-tools to Dotkernel API , we need to analyze the core components
of it.

## Database

- there is a database in the current API ?
- which is the connection to database
- which library is used for database interaction ( laminas-db, doctrine 2, eloquent, or else )

### Note

> Dotkernel API is tested only with MariaDB version 10.6 and 10.11 LTS

## Authentication and Authorization

- how authentication is done ? (basic, digest, oauth2, etc.)
- how authorization is done ?  (acl, rbac)

## Modules

- analyze configuration files of the modules (what needs to be configured in order to use a module)
- analyze routes (which are the routes, protection rules, which one need auth, etc.)
- analyze response format (content negotiation and validation, which ones are json, hal, views, etc.)
- analyze input field validations

## Custom functionalities

Analyze the custom code (code that cannot be generated through Admin UI and require manual implementation)

For instance:

- caching
- events
- services
- extra installed packages and libraries
- jobs and queues
- third-parties
- tests
