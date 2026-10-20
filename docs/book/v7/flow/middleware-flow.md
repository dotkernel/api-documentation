# Middleware flow

## Summary

A diagram of the default middleware pipeline in Dotkernel API, showing the order in which middlewares process an incoming request and the response on the way back out.

## Details

The graph below demonstrates a default flow between Dotkernel's middlewares.

![Dotkernel API Middleware Flow!](https://docs.dotkernel.org/img/api/v7/dotkernel-middleware-flow.png)

> Dotkernel API 7.1 and 7.2 pipe `MalformedRequestBodyMiddleware` right after `ProblemDetailsMiddleware`, so a malformed request body produces a `400 Bad Request` response.
> The diagram does not show it, and version 7.0 does not have it.
> Update to the latest Dotkernel API 7.x release to get it, see [Upgrading from 7.0 to 7.1](../upgrading/UPGRADE-7.1.md).

## FAQ

**Q: Why does middleware order matter?**

A: Each middleware may short-circuit the request or add attributes the next one relies on.
For example, authentication must run before authorization, because authorization needs the resolved identity.

**Q: Where is the pipeline defined?**

A: In `config/pipeline.php`.
That file is the authoritative order; the diagram is a visual summary of it.

**Q: How do I add my own middleware?**

A: Register it in the container, then insert it into `config/pipeline.php` at the position where it needs to run, or attach it to a specific route.

**Q: Which middlewares are responsible for authentication and authorization?**

A: `AuthenticationMiddleware` and `AuthorizationMiddleware`.
See [Authentication](../core-features/authentication.md) and [Authorization](../core-features/authorization.md).

**Q: What happens when a middleware throws?**

A: The error handling middleware at the top of the pipeline converts it into a Problem Details response.
See [Problem Details](../extended-features/problem-details.md).
