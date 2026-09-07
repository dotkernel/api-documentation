# Production Readiness

## Summary

Dotkernel API ships a complete application: a PSR-15 middleware pipeline, OAuth2 authentication, RBAC, input filtering, problem-details error responses and the OpenAPI attribute sources you [generate the specification from](../openapi/generate-documentation.md).
What it does not ship is the operational layer a public API needs around it — rate limiting, API gateway integration, federated identity, log shipping and error tracking, health checks, metrics and response caching.
This page names each gap, says whether it belongs in the application or in the platform in front of it, and gives the concrete thing to configure until Dotkernel provides one.
It also covers the one thing that does ship and has to go before launch: the demo credentials the Doctrine fixtures seed.

## Details

The project README describes Dotkernel API as "a production-ready REST API application", and that is fair as far as it goes.
The request path, the persistence layer and the security primitives are finished and tested.

The gaps below are not defects.
Most of them sit on the boundary between an application and the platform it runs on, and several belong there deliberately — [issue #529](https://github.com/dotkernel/api/issues/529) says exactly that about rate limiting.
The problem is that the boundary is nowhere documented, so it is easy to deploy Dotkernel API believing the missing half came in the box.

Everything on this page was checked against the [dotkernel/api](https://github.com/dotkernel/api) repository on the `7.0` branch.

### At a glance

| Concern | Ships with the API | Where it belongs | Tracked upstream |
| --- | --- | --- | --- |
| Default demo credentials | Yes — seeded by the Doctrine fixtures | Removed before launch | — |
| Rate limiting and throttling | No | Proxy, gateway, or a PSR-15 middleware | [#529](https://github.com/dotkernel/api/issues/529) |
| API gateway integration | No | Platform | — |
| External OAuth2 / OIDC provider | No — the API is its own authorization server | Application | — |
| Error tracking (Sentry and similar) | No — local files only | Application | — |
| Access logs, request IDs, tracing | No | Application and platform | — |
| Health and readiness endpoints | No | Application | [#492](https://github.com/dotkernel/api/issues/492) |
| Metrics (Prometheus, OpenTelemetry) | No | Application | — |
| HTTP response caching | No | Application and CDN | [#443](https://github.com/dotkernel/api/issues/443) |
| Background jobs and async work | No — mail is sent in-request | Separate service | — |
| Secrets management | No — cleartext PHP config | Platform | — |
| Scheduled dependency re-audit | No — Composer audits at resolution time, not on a schedule | Server | [#525](https://github.com/dotkernel/api/issues/525) |
| OpenAPI specification file | No — attribute sources only, generated on demand | Build step | — |

## Default credentials

Every other entry on this page is something missing.
This one is something present: the Doctrine fixtures seed four sets of credentials, and all four are published in the repository.

| Seeded by | Identity | Secret |
| --- | --- | --- |
| `AdminLoader` | `admin` | `dotadmin` |
| `UserLoader` | `test@dotkernel.com` | `dotkernel` |
| `OAuthClientLoader` | `admin` | `admin` |
| `OAuthClientLoader` | `frontend` | `frontend` |

The password grant needs both halves, a client and an account, and the fixtures supply a matching pair of each.
Running `php ./bin/doctrine fixtures:execute` against a production database therefore leaves `/security/generate-token` answering to the `admin` client with secret `admin` and the `admin` account with password `dotadmin`, which between them reach every administrator endpoint in the API.

Change them before you seed, by editing the loaders in `src/Core/src/App/src/Fixture/`:

- `AdminLoader.php` and `UserLoader.php` — `setIdentity()` for the identity, `usePassword()` for the password, and optionally `setFirstName()` and `setLastName()`.
- `OAuthClientLoader.php` — `setName()` and `setSecret()`.

If a database has already been seeded, treat all four as public.
Create a replacement administrator with `php ./bin/cli.php admin:create`, then remove the `admin` and `test@dotkernel.com` accounts and re-secret both OAuth clients.

[Basic Security](../security/basic-security.md) covers the demo accounts and [OAuth2 Security](../security/oauth2-security.md) the clients.

## Rate limiting

Nothing in `config/pipeline.php` limits request rates, none of the required packages provides throttling, and there is no rate-limit configuration anywhere in the repository.
Every endpoint accepts requests as fast as the web server will serve them.

Two endpoints make this matter more than it would on a read-only API.

- `POST /security/generate-token` is unauthenticated and verifies a password hash on every call, which makes it both a brute-force target and an expensive one.
    Nothing counts failed attempts: the `AdminLogin` entity models a login record, but no lockout or backoff is applied on repeated failures, and there is no equivalent record for regular users.
- `POST /error-report` is guarded by a static token plus a domain or IP whitelist, and appends every accepted report to a single file.
    A leaked token with no rate limit in front of it is a way to fill a disk.

### What to do instead

Put the limit in front of the application wherever you can.

- **nginx** — a `limit_req_zone` keyed on `$binary_remote_addr`, with a tighter zone applied to the `/security/` location than to the rest of the API.
- **Apache** — `mod_ratelimit` for bandwidth, or `mod_qos` for request-rate and concurrency limits.
- **API gateway or CDN** — per-consumer quotas, which is the only place you can enforce a limit per API key across several application nodes.

If you need the limit inside the application — per authenticated identity rather than per IP address, for instance — add a PSR-15 middleware.
Issue #529 names [`nikolaposa/rate-limit`](https://github.com/nikolaposa/rate-limit) as the candidate.
Register it in `config/pipeline.php` after `RouteMiddleware`, so you can vary the limit by matched route name, and after `AuthenticationMiddleware` if you want to key the limit on the identity.

Keep in mind that `dot-response-header` sets fixed values per route, so it cannot emit a running count.
`RateLimit-Limit`, `RateLimit-Remaining`, `RateLimit-Reset` and `Retry-After` have to come from the middleware itself.

## API gateway

The repository carries no gateway configuration and nothing in the application expects to sit behind one.
Putting a gateway in front of Dotkernel API is entirely your own work, and it is where you would add TLS termination, per-consumer quotas and API keys, request and response transformation, edge caching and a WAF.

Two things to get right when you do.

**Do not double up on headers.**
CORS is answered inside the application by `mezzio/mezzio-cors` from the CORS local config, and the static security headers — `X-Content-Type-Options`, `Referrer-Policy` and `permissions-policy` — come from `dot-response-header` via `config/autoload/response-header.global.php`.
If the gateway adds its own copies of these, clients receive duplicates.
Decide which layer owns each header and disable it in the other.

**`X-Forwarded-For` is trusted without a proxy allowlist.**
`Core\App\Service\IpService::getUserIp()` returns `HTTP_X_FORWARDED_FOR` whenever that value parses as a public IP address, then falls back to `HTTP_CLIENT_IP`, and only then to `REMOTE_ADDR`.
There is no trusted-proxy list and no check that the request actually arrived through your proxy, so a client can set either header and choose the address the application sees.
That address is what the error reporting endpoint checks against its `ip_whitelist`.
Configure your proxy to **overwrite** `X-Forwarded-For` with the real peer address rather than appending to it, and strip `Client-IP` at the edge.

A gateway also wants a health probe, and there is nowhere useful to point it — see the next section.

## External OAuth2 server integration

Dotkernel API is its own authorization server.
`mezzio/mezzio-authentication-oauth2` wraps `league/oauth2-server`, tokens and clients live in the application's own `oauth_*` tables through the repositories in `src/Core/src/Security/`, and the RSA key pair used to sign them sits in `data/oauth`.
See [Authentication](../core-features/authentication.md) and [OAuth2 Security](../security/oauth2-security.md).

There is no support for delegating that to an external identity provider.
Specifically, the API has no OpenID Connect layer — no discovery document, no JWKS endpoint, no `id_token` — no token introspection (RFC 7662) or revocation (RFC 7009) endpoint, and no way to accept an access token minted by Keycloak, Auth0, Microsoft Entra ID, Amazon Cognito or Okta.
Social login exists in the Dotkernel ecosystem as [`dot-auth-social`](https://github.com/dotkernel/dot-auth-social), but that is a Facebook integration for Dotkernel Frontend and is not wired into the API.
Multi-factor authentication is the same story: [`dot-totp`](https://github.com/dotkernel/dot-totp) is a standalone component with no API integration.

To federate today you write the bridge yourself: a middleware placed before `Api\App\Middleware\AuthenticationMiddleware` that validates the provider's JWT against its published JWKS, then maps the claims onto a local identity implementing `Mezzio\Authentication\UserInterface`.
The application's RBAC then works unchanged, because `AuthorizationMiddleware` only reads the role off that identity.

### Grants you did not ask for are enabled

This one is worth checking before you go live even if you never federate.

`mezzio/mezzio-authentication-oauth2` enables five grants by default — client credentials, password, authorization code, implicit and refresh token — and the main local config sets no `grants` key, so Dotkernel API inherits all five.
Both `/security/generate-token` and `/security/refresh-token` route to the package's `TokenEndpointHandler`, which dispatches on the `grant_type` field in the request body.
The documentation covers the password grant only.

Disable the grants you do not use by setting them to `null` under the `authentication` key in your main local config:

```php
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;

return [
    'authentication' => [
        'grants' => [
            ImplicitGrant::class          => null,
            AuthCodeGrant::class          => null,
            ClientCredentialsGrant::class => null,
        ],
    ],
];
```

The implicit grant is the one to remove first: it returns tokens in the URL fragment and is removed in OAuth 2.1.
The password grant that Dotkernel API documents is dropped in OAuth 2.1 as well.
That is defensible for a first-party client over TLS, but it does mean third-party clients cannot use the authorization-code-with-PKCE flow they expect.

## Logging and error tracking

What ships is a single file writer.
`config/autoload/error-handling.global.php` enables `dot-errorhandler` with `loggerEnabled` set to `true` and its `logger` pointing at the container service `dot-log.default_logger`, and defines that logger as one `stream` writer with a JSON formatter writing to `log/error-log-{Y}-{m}-{d}.log`.
The error reporting endpoint appends separately to `log/error-report-endpoint-log.log`.

That is the whole logging story, and the gaps follow from it.

- **Files only, never standard output.**
    The shipped configuration points the stream at a path under `log/`.
    Anything that collects `stdout` and `stderr` rather than reading files sees nothing at all, so either ship the directory with your own agent or repoint the stream at `php://stderr` yourself.
- **No rotation and no retention.**
    The `{Y}-{m}-{d}` pattern opens a new file each day and never removes an old one.
    Add `logrotate` or a scheduled cleanup, or the log directory grows without bound.
- **No external error tracking.**
    Neither `dot-errorhandler` nor `dot-log` has a Sentry, Rollbar, Bugsnag or Datadog integration, and none of those appears anywhere in the repository.
    An unhandled exception produces a problem-details response for the client and a line in a file on that one server.
    Nobody is paged, nothing is grouped, and there is no release or regression tracking.
- **No access log.**
    Nothing records completed requests — method, matched route, status, duration, identity.
    Your web server's access log is all you get, and it knows neither the route name nor the authenticated user.
- **No correlation identifier.**
    A problem-details response carries no request id, so a user's bug report cannot be tied back to a log line, and nothing propagates or generates a `traceparent` header across services.

### Wiring Sentry in today

The contract between `dot-errorhandler` and its logger is PSR-3, and `logger` is a container service name.
That is the whole integration point: register any PSR-3 logger and name it.

```php
// config/autoload/dependencies.global.php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sentry\Monolog\Handler as SentryHandler;
use Sentry\State\HubAdapter;

return [
    'dependencies' => [
        'factories' => [
            'app.error_logger' => function (): Logger {
                $logger = new Logger('api');
                $logger->pushHandler(new SentryHandler(HubAdapter::getInstance()));
                $logger->pushHandler(new StreamHandler('php://stderr'));

                return $logger;
            },
        ],
    ],
];
```

Then point the error handler at it, from your main local config so the change stays out of version control:

```php
return [
    'dot-errorhandler' => [
        'loggerEnabled' => true,
        'logger'        => 'app.error_logger',
    ],
];
```

Register a **new** service rather than redefining `dot-log.default_logger`.
`Core\App\Service\MailService` injects that service and type-hints `Dot\Log\LoggerInterface`, so replacing it with a plain PSR-3 logger breaks mail sending.

## Health checks, metrics and tracing

There is no health or readiness endpoint.
`GET /` is handled by `Api\App\Handler\GetIndexResourceHandler`, which reads the application name and version out of configuration and returns them.
It touches neither the database nor any downstream service, so a `200` from it means PHP is running and nothing more.
It also carries a `#[ResourceDeprecation]` attribute with a sunset date of 2038-01-01, present purely as an example of the deprecation feature, so every response to it is decorated with deprecation headers.
It is not a health endpoint and should not be used as one.

A readiness check for this application needs to cover at least the database connection, whether migrations are at head, write access to `log/` and `data/cache`, read access to the three files in `data/oauth`, and the mail transport if you send mail during requests.
Issue #492 is the open RFC; until it lands, add your own handler and route.

There is no metrics or tracing support either — no Prometheus endpoint, no OpenTelemetry instrumentation, no APM integration.
You can see traffic at the web server and nothing inside it: no per-route latency, no database query counts, no error rate broken down by endpoint.

## Caching

`dotkernel/dot-cache` is a required dependency and its `ConfigProvider` is registered in `config/config.php`, but nothing in the request path uses it.
The caching that *is* configured is configuration caching: `ConfigAggregator::ENABLE_CACHE` in the main local config, writing `data/cache/config-cache.php`, cleared with `php bin/clear-config-cache.php`.
That is a boot-time optimization, not response caching.

At the HTTP level there is no `ETag`, no `Last-Modified` and no conditional-request handling, so clients cannot revalidate and every request transfers a full representation.
`Cache-Control` is set on exactly two routes — `security::generate-token` and `security::refresh-token`, both to `no-store`.
Every other response leaves caching headers unset, which means intermediaries fall back to their own heuristics on responses that are usually per-identity.
Set a conservative default under the `'*'` key in `config/autoload/response-header.global.php` and relax it only where you actually want caching.

Doctrine's second-level cache and result cache are not configured either, so a collection endpoint queries the database on every request.
Issue #443 is the open RFC to extend caching in the API.

## Background work

Everything happens inside the request.
`Core\App\Service\MailService` calls `dot-mail` synchronously, so account activation, password recovery and password reset emails hold the HTTP connection open for the length of the SMTP conversation, and a transport failure surfaces to the client as a failed request.
There is no queue, no scheduler beyond what you add to `cron`, and no retry.

Dotkernel publishes a separate queue project — [Dotkernel Queue](https://github.com/dotkernel/queue), built on Mezzio, Symfony Messenger and Swoole — but it is not a dependency of the API and there is no documented integration between the two.
Until there is, either accept in-request sending or dispatch to a worker you wire up yourself.

## Configuration and secrets

- **Configuration is cleartext PHP on disk.**
    The main local config carries database credentials and the mail local config carries SMTP credentials, both readable by the web server user.
    Nothing in the shipped configuration reads environment variables — `getenv()` works, but every call is yours to add — and there is no integration with a secrets manager.
- **OAuth keys are local files.**
    The signing key pair lives at `data/oauth`, so if you run more than one application node they must all hold the same pair, or a token issued by one is rejected by another.
    Nothing in the project shares them, and regenerating them invalidates every issued token.
- **Dependency auditing needs a schedule, not a switch.**
    Composer audits by default as of 2.10: `policy.advisories.block` and `policy.malware.block` are both `true`, so a resolution refuses a version with a known advisory and refuses a package flagged as malware, while `composer audit` defaults to failing on advisories, malware and abandoned packages.
    `roave/security-advisories` predates that mechanism and is now largely redundant with it — and being a `require-dev` entry, it is absent from a production `composer install --no-dev` anyway.
    Dotkernel API does not commit `composer.lock`; it is listed in `.gitignore` and generated on each server by the first `composer install`, which reports `No composer.lock file present. Updating dependencies to latest instead of installing from lock file` and resolves rather than installing from a lock.
    That resolution is the code path advisory blocking fires on, so **every fresh install is audited as it happens** — against whatever was current on that server that day, which is also why two servers built a month apart can end up on different versions.
    What nothing does is re-check a lock that has stopped changing.
    A server installed once and left alone has never been re-examined against advisories published since, so run `composer audit` there on a schedule rather than only at install time.
    Issue #525 proposes pinning the policy explicitly in `composer.json` rather than inheriting the defaults.

## OpenAPI specification

The attributes ship; the specification file does not.
Every path, schema and security scheme is declared across four files — `src/App/src/OpenAPI.php`, `src/Admin/src/OpenAPI.php`, `src/Security/src/OpenAPI.php` and `src/User/src/OpenAPI.php` — and `public/` contains no `openapi.yaml` or `openapi.json`.
Producing one is a step you add to your own build or deploy:

```shell
./vendor/bin/openapi ./src --output public/openapi.yaml
```

`zircote/swagger-php` is a `require` rather than a `require-dev` dependency, so `vendor/bin/openapi` is present on a production install as well.

Two things to get right.

**The server URL defaults to localhost.**
`src/App/src/OpenAPI.php` declares `#[OA\Server(url: 'http://api.dotkernel.localhost')]`, so a specification generated without editing that line tells every client to call your development host.

**The file is a snapshot.**
No Composer script wraps the command and nothing regenerates the file when the attributes change, so a specification generated once drifts from the API it describes.
Regenerate it in the same step that deploys the code.

See [Generate documentation](../openapi/generate-documentation.md) for the version and format options, and [Render documentation](../openapi/render-documentation.md) for serving the result.

## A minimum before you go live

Ordered by what bites first:

1. Change every seeded credential — both demo accounts and both OAuth clients — or delete them once a real administrator exists.
2. Rate-limit `/security/generate-token` and `/error-report` at the proxy.
3. Disable the OAuth2 grants you do not use, starting with the implicit grant.
4. Point `dot-errorhandler` at a logger that reaches somebody — Sentry, or at least a shipped `stderr` stream.
5. Add `logrotate` for `log/`, or repoint the writer at standard output.
6. Add a readiness endpoint that checks the database, and point your load balancer at it rather than `/`.
7. Fix `X-Forwarded-For` handling at the proxy, since the application trusts the header as sent.
8. Set a default `Cache-Control` for the whole API instead of leaving it unset.
9. Schedule `composer audit` on each server — the install that generated its lock was audited, but nothing re-checks that lock afterwards.
10. Work through [Basic Security](../security/basic-security.md) and [OAuth2 Security](../security/oauth2-security.md), which cover the application-level hardening this page does not repeat.

## FAQ

**Q: Is the README wrong to call Dotkernel API production-ready?**

A: No.
The application is complete and tested; what is missing is the operational layer around it, most of which is conventionally the platform's job.
The gap is documentation, not code — nothing tells you which half you still have to build.

**Q: What are the default credentials, and where do I change them?**

A: `admin` / `dotadmin` and `test@dotkernel.com` / `dotkernel` for the accounts, and OAuth clients `admin` / `admin` and `frontend` / `frontend`.
All four come from the fixture loaders in `src/Core/src/App/src/Fixture/`, so edit those before running `php ./bin/doctrine fixtures:execute`, or replace the records afterwards.

**Q: Which of these should I solve in the application rather than the platform?**

A: External identity provider integration, error tracking, the health endpoint, caching headers and asynchronous mail.
Rate limiting, TLS, secrets and log shipping belong in the platform, though rate limiting per authenticated identity can only be done in the application.

**Q: Can I add rate limiting without waiting for issue #529?**

A: Yes.
Do it at nginx, Apache or your gateway if the key is the client IP address.
If the key is the authenticated identity, add a PSR-15 middleware to `config/pipeline.php` after `AuthenticationMiddleware` — `nikolaposa/rate-limit` is the package named in that issue.

**Q: How do I get errors into Sentry today?**

A: Register your own PSR-3 logger service with a Sentry handler and set it as `dot-errorhandler.logger`.
Do not redefine `dot-log.default_logger`, because `MailService` type-hints `Dot\Log\LoggerInterface` on it.

**Q: Which OAuth2 grants are actually enabled?**

A: All five that `mezzio/mezzio-authentication-oauth2` ships — client credentials, password, authorization code, implicit and refresh token — because the application never sets a `grants` key.
Only the password and refresh token grants are documented.
Set the rest to `null` if you do not use them.

**Q: Is there a health check endpoint for my load balancer?**

A: Not yet; issue #492 is the open RFC.
`GET /` only reports the application name and version, and carries example deprecation headers, so it tells you nothing about the database.
Write your own handler until the RFC lands.

**Q: Where do logs go, and how do I ship them?**

A: To `log/error-log-{Y}-{m}-{d}.log` on the application server, JSON-formatted, with nothing rotating them.
Ship them with your own agent, or repoint the `stream` writer in `config/autoload/error-handling.global.php` at `php://stderr` if your collector reads standard error.
