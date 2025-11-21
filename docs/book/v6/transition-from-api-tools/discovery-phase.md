# Discovery phase for a current system built using API Tools [WIP]

To transition a system built using api-tools to Dotkernel API, we need to analyze the core components of it.

## Database

- is there a database in the current API?
- which is the connection to a database
- which library is used for database interaction (laminas-db, doctrine 2, eloquent, or else)?

> Dotkernel API is tested only with MariaDB version 10.6, 10.11 LTS, 11.4 LTS, and 11.8 LTS

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
