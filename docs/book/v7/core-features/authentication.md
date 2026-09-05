# Authentication

## Summary

Dotkernel API authenticates with the OAuth2 password grant through `mezzio/mezzio-authentication-oauth2`.
Clients exchange credentials at `POST /security/generate-token` for an access token and a refresh token, then send the access token in the `Authorization` header; `POST /security/refresh-token` renews it.
Requests without an `Authorization` header get a default `guest` identity, which can reach only public endpoints.

## Details

Authentication is the process by which an identity is presented to the application.
It ensures that the entity making the request has the proper credentials to access the API.

**Dotkernel API** identities are delivered to the application from the client through the `Authorization` request.
If it is present, the application tries to find and assign the identity to the application.
If it is not presented, Dotkernel API assigns a default `guest` identity, represented by an instance of the class `Mezzio\Authentication\UserInterface`.
Guests can access public endpoints but cannot access protected resources (those requiring user or admin roles).
Check out the [Authorization page](https://docs.dotkernel.org/api-documentation/v7/core-features/authentication/) for more details on role-based access.

## Configuration

Dotkernel API uses the **OAuth2 password grant flow** for authentication.
This allows users to exchange their credentials (username/password) for access tokens.
These tokens are then used for later requests instead of repeatedly sending credentials.

Authentication in Dotkernel API is built around the `mezzio/mezzio-authentication-oauth2` component and is already configured out of the box.
To customize authentication behavior (token lifetimes, algorithms, etc.), edit `config/autoload/local.php` under the `authentication` key.
See the [Mezzio OAuth2 documentation](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration) for all available options.

> You can check the [mezzio/mezzio-authentication-oauth2](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration) configuration part for more info.

## How it works

Dotkernel API authentication system can be used for SPAs (single-page applications), mobile applications, and simple, token-based APIs.
It allows each user of your application to generate API tokens for their accounts.

The authentication happens through the middleware in the `Api\App\Middleware\AuthenticationMiddleware`.

### Database

When you install **Dotkernel API** for the first time, you need to run the migrations and seeders (fixtures).
All the tables required for authentication are automatically created and populated.

```shell
php ./vendor/bin/doctrine-migrations migrate
php ./bin/doctrine fixtures:execute
```

The commands above create OAuth tables (oauth_clients, oauth_scopes, oauth_*) and seed the initial credentials:

- **Admin**: identity=admin, password=dotadmin
- **User**: identity=test@dotkernel.com, password=dotkernel

Check out the [Installation Guide](https://docs.dotkernel.org/api-documentation/v7/installation/doctrine-orm/) for more details.

In Dotkernel API, authenticated users come from either the `admin` or the `user` tables.
We chose to keep the admin and user tables separate to prevent users of the application from accessing sensitive data that only administrators should access.

Another table that is pre-populated is the `oauth_scopes` table, with the `api` scope.

### Issuing API Tokens

Token generation in Dotkernel API is done using the `password` `grant_type` scenario, which in this case allows authentication to an API using the user's credentials (generally a username and password).

The `POST /security/generate-token` endpoint accepts OAuth2 credentials and returns both an access token (for making API calls) and a refresh token (for refreshing expired access tokens).

The client requires the following parameters:

| Field           | Type   | Purpose                                                  | Example                |
|-----------------|--------|----------------------------------------------------------|------------------------|
| `grant_type`    | string | OAuth2 flow type                                         | `"password"`           |
| `client_id`     | string | OAuth client identifier from the `oauth_clients` table   | `"frontend"`           |
| `client_secret` | string | OAuth client credential from the `oauth_clients` table   | `"frontend"`           |
| `scope`         | string | Permission scope from the `oauth_scopes` table           | `"api"`                |
| `username`      | string | User identity (email/username) from table `admin`/`user` | `"test@dotkernel.com"` |
| `password`      | string | User password                                            | `"dotkernel"`          |

This is what the call should look like:

```shell
POST /security/generate-token HTTP/1.1
Accept: application/json
Content-Type: application/json
{
  "grant_type": "password",
  "client_id": "frontend",
  "client_secret": "frontend",
  "scope": "api",
  "username": "test@dotkernel.com",
  "password": "dotkernel"
}
```

The server responds with a JSON as follows:

```json
{
    "token_type": "Bearer",
    "expires_in": 86400,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "refresh_token": "def5020087199939a49d0f2f818..."
}
```

| Field           | Type    | Lifetime                             | Purpose                               |
|-----------------|---------|--------------------------------------|---------------------------------------|
| `token_type`    | string  | N/A                                  | Header value type                     |
| `expires_in`    | integer | N/A                                  | Seconds until access token expires    |
| `access_token`  | string  | 86400 seconds = 1 day (configurable) | Used in `Authorization` header        |
| `refresh_token` | string  | 1 month (configurable)               | Used to refresh expired access tokens |

On later requests to the server for an authenticated endpoint, the client should use the `Authorization` header request.

```shell
GET /users/1 HTTP/1.1
Accept: application/json
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

### Refreshing tokens

When an access token expires, use the refresh token (received during token generation) to obtain a new access token without re-entering credentials:

```shell
POST /security/refresh-token
Accept: application/json
Content-Type: application/json
{
  "grant_type": "refresh_token",
  "client_id": "frontend",
  "client_secret": "frontend",
  "scope": "api",
  "refresh_token": "def5020087199939a49d0f2f818..."
}
```

The response contains a new `access_token` and `refresh_token`:

```json
{
  "token_type": "Bearer",
  "expires_in": 86400,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "def5020087199939a49d0f2f818..."
}
```

## Common Issues

### "Invalid credentials" error

- Check username/password are correct.
- Verify client_id and client_secret match OAuth client in the database.
- Confirm the user account exists and is active.

### "Token has expired" error

- Use refresh_token to get a new access_token.
- If refresh_token is expired, re-authenticate with credentials.

### "Invalid scope" error

- Verify `scope` field is set to `"api"` (the only configured scope)

## Flow Diagram

```quote
User Credentials
      ↓
POST /security/generate-token
      ↓
Access Token + Refresh Token
      ↓
Store tokens securely  ←─────────────────────────┐
      ↓                                          |
Include Access Token in Authorization header     |
      ↓                                          |
Make API requests                                |
      ↓                                          |
Token expires?                                   |
      ↓ (yes)                                    |
POST /security/refresh-token with refresh_token  |
      ↓                                          |
Get new Access Token  ───────────────────────────┘
```

## Security Best Practices

- **Store tokens securely**: Never commit tokens to version control. Use environment variables or secure storage.
- **Use HTTPS only**: OAuth2 tokens should only be transmitted over HTTPS in production.
- **Rotate credentials**: Change default OAuth client secrets in production.
- **Token expiration**: Access tokens expire (default 1 day). Implement refresh logic in clients.
- **Never expose refresh tokens**: Refresh tokens should only be stored client-side, never in logs or public code.

## FAQ

**Q: What happens if a request sends no `Authorization` header?**

A: The application assigns a default `guest` identity, an instance of `Mezzio\Authentication\UserInterface`.
Guests can reach public endpoints but not protected ones.

**Q: Which OAuth2 grant does the API use?**

A: The password grant: credentials are exchanged once for tokens, and subsequent requests carry the access token instead of the credentials.

**Q: What must I run before authenticating for the first time?**

A: The migrations and fixtures — `php ./vendor/bin/doctrine-migrations migrate` followed by `php ./bin/doctrine fixtures:execute` — which create the OAuth tables and seed the initial credentials.
See [Doctrine ORM](../installation/doctrine-orm.md).

**Q: What are the seeded credentials?**

A: `admin` / `dotadmin` for the admin account and `test@dotkernel.com` / `dotkernel` for the user account.
Remove or change them before production.
See [Basic security](../security/basic-security.md).

**Q: Why are admins and users in separate tables?**

A: To keep application users away from data that only administrators should reach.
Authenticated identities therefore come from either the `admin` or the `user` table.

**Q: Which parameters does the token request need?**

A: `grant_type`, `client_id`, `client_secret`, `scope`, `username` and `password`.
The client values come from `oauth_clients` and the scope from `oauth_scopes`.

**Q: How long do the tokens last?**

A: Access tokens expire after one day and refresh tokens after one month, both configurable under the `authentication` key in `config/autoload/local.php`.

**Q: How do I renew an expired access token?**

A: Post the `refresh_token` to `/security/refresh-token` with `grant_type` set to `refresh_token`.
If the refresh token has expired too, authenticate again with credentials.

**Q: I get an "Invalid scope" error. What is wrong?**

A: `scope` must be `"api"` — it is the only configured scope.

**Q: I get "Invalid credentials" even though the password is right. What else could it be?**

A: A mismatched `client_id` or `client_secret` against the `oauth_clients` table, or an account that does not exist or is inactive.

**Q: Which middleware performs authentication?**

A: `Api\App\Middleware\AuthenticationMiddleware`, which runs before authorization in the pipeline.
See [Middleware flow](../flow/middleware-flow.md).

**Q: Is authenticating enough to access an endpoint?**

A: No.
Authentication establishes the identity; the role still needs permission for the route.
See [Authorization](authorization.md).
