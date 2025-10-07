# Basic Security

Dotkernel API provides all necessary tools to implement safe applications, however you will need to manually make use of some of them.
This section will go over the provided tools and any steps you need to follow in order to use them successfully, as well as a few general considerations.

## User Input Validation

In order to validate user input, Dotkernel API makes use of [laminas/laminas-inputfilter](https://github.com/laminas/laminas-inputfilter).
It is strongly recommended that custom functionality parsing user input also make use of input filters to validate the data.

## Content Negotiation

Content negotiation in Dotkernel API is done by a middleware configured using the `config/autoload/content-negotiation.global.php` file.

Whenever an endpoint needs custom `Accept` and/or `Content-Type`, make sure that you set them in the above file.

> Read more about [content negotiation](https://www.dotkernel.com/dotkernel-api/content-negotiation-in-dotkernel-rest-api/) and the way it is implemented in [Dotkernel API](../core-features/content-validation.md).

## Cross-Origin Resource Sharing

Dotkernel API uses [mezzio/mezzio-cors](https://github.com/mezzio/mezzio-cors) to handle CORS details.
The default configuration, found in `config/autoload/cors.local.php`, makes the application accessible by any origin.

Make sure your application specifies only the required origins when in a production environment.

> This step is described in the [CORS](../tutorials/cors.md) tutorial.

## Role-Based Access Control

This project makes use of [mezzio/mezzio-authorization-rbac](../core-features/authorization.md) to handle access control.

The default use cases have already been configured, but any custom functionality will require additional configuration to make sure it is protected.
Update the configuration file of this package (`config/autoload/authorization.global.php`) whenever you add new routes or roles.

## Demo Credentials

Dotkernel API ships with two demo accounts: an admin account (`admin`) and a user account (`test@dotkernel.com`),
with public identities and passwords as described in the [token authentication tutorial](https://docs.dotkernel.org/api-documentation/v6/tutorials/token-authentication/).

Make sure to **update** or **remove** these demo accounts in your production environment.

## Error Reporting Endpoint and ErrorReportingTokens

The error reporting endpoint (`/error-report`) is intended to be used by third parties to report errors back to your application.
This endpoint requires its own token type, namely an `ErrorReportingToken`, that is to be added in the configuration file for every application sending reports.

Since these tokens do not have an expiration date, consider periodically refreshing them manually.

This feature is configured using the `config/autoload/error-handling.global.php` file, under the configured `ErrorReportServiceInterface::class` key.

This file is visible to your VCS by default, so take care not to overwrite tokens locally and commit them to your production environment.
Additionally, make sure the `ip_whitelist` or `domain_whitelist` keys are set to your desired values, especially when in a production environment.

> Read more about the [error reporting](../core-features/error-reporting.md) feature.

## OpenAPI Documentation

In order to provide an interactive documentation, Dotkernel API implemented [zircote/swagger-php](https://github.com/zircote/swagger-php).

Make sure **not** to include sensitive data as examples for any of the documented endpoints.
It is not recommended to enable the documentation in a production environment.

> Read more about the [OpenAPI documentation](../openapi/introduction.md).

## PHP Dependencies

Dotkernel API uses `composer` to handle PHP dependencies.
In time, make sure to review any common vulnerabilities and exposures for your dependencies.

> You may also keep an eye on the Dotkernel API changelog for any updates relevant to your project.

## General Considerations

- `*.global.php` and `*.php.dist` configuration files are visible to the VCS, make sure **not** to include sensitive data in commits.
    - `*.local.php` configuration files are ignored by the VCS by default and are the recommended place for sensitive data such as API keys.
- Make sure the `development mode` is correctly set - **do not** enable `development mode` in a production environment.
    - You can use the following command to check the current status:

```shell
composer development-status
```

- Dotkernel API ships with a [Laminas Continuous Integration](https://github.com/laminas/laminas-continuous-integration-action) GitHub Action,
  if you are using a public repository consider keeping it in your custom applications to ensure code quality.

> Read more about using [Laminas Continuous Integration](https://getlaminas.org/blog/2024-08-05-using-laminas-continuous-integration.html).
