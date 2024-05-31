# Authentication

Authentication is the process by which an identity is presented to the application. It ensures that the entity
making the request has the proper credentials to access the API.

DotKernel's API identities are delivered to the application from the client through the `Authorization` request
header.  If it is present, the application tries to find and assign the identity to the application. If it is not presented,
DotKernel's API assigns a default `guest` identity, represented by an instance of the class
`Mezzio\Authentication\UserInterface`.

## Configuration

DotKernel's API authentication is made around `mezzio/mezzio-authentication-oauth2` component and is already configured.
with what is necessary in order to work. But if you want to dig more, the configuration is hold on.
`config/autoload/local.php` under the authentication key.

> You can check the [mezzio/mezzio-authentication-oauth2](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration)
> configuration part for more digging.

## How it works

DotKernel's API authentication system can be used for SPAs (single-page applications), mobile applications, and
simple, token-based APIs. It allows each user of your application to generate API tokens for their accounts.

The authentication happens through the middleware in the `Api\App\Middleware\AuthenticationMiddleware`.

## Database

When DotKernel API is installed for the first time, and you run the migrations and seeders, all the tables
needed for authentication are automatically created and inserted with the data needed for authentication.

In DotKernel's API, authentication users can be from the `admin` table and from the `users` table. We choose to keep the admin
table separated from the users to prevent users of the application from accessing sensitive data, which only the administrators
of the application should access.

Knowing this, upon migrations, the `oauth_clients` table is pre-populated with the default `admin` and `frontend` clients with
the same password as their names. (you can change those passwords.).

As you guested each client serves to authenticate `admin` or `users`.

Another table that is pre-populated is the `oauth_scopes` table, with the `api` scope.

### Issuing API Tokens

In DotKernel's API, generating tokens is done using the `password` `grand_type` scenario, which in this case allows authentication
to an API using the user's credentials (generally a username and password).

The client sends a POST request to the `/security/generate-token` with the following parameters:

- `grant_type` = password.
- `client_id` with the client name (from `oauth_clients` table).
- `client_secret` with the client secret (password from `oauth_clients` table for the client).
- `scope` with the scope from `oauth_scopes` table.
- `username` with the user’s username.
- `password` with the user’s password.

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

```php
{
"token_type": "Bearer",
"expires_in": 86400,
"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
"refresh_token": "def5020087199939a49d0f2f818..."
}
```

Next time when you make a request to the server to an authenticated endpoint, the client should use
the `Authorization` header request.

```shell
GET /users/1 HTTP/1.1
Accept: application/json
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

### Refreshing tokens

DotKernel's API provides the ability to refresh the access token, generating a new one.

The clients need to send a `POST` request to the `/security/refresh-token` with the following request

```shell
POST /security/refresh-token HTTP/1.1
Accept: application/json
Content-Type: application/json
{
"grant_type": "refresh_token",
"client_id": "frontend",
"client_secret": "frontend",
"scope": "api",
"refresh_token" : "def5020087199939a49d0f2f818..."
}
```

The server responds with a JSON as follows:

```php
{
"token_type": "Bearer",
"expires_in": 86400,
"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
"refresh_token": "def5020087199939a49d0f2f818..."
}
```