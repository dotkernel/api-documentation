# A practical example: Find a user by identity

## Summary

A worked example of adding an endpoint by following an existing one.
Starting from `user::view-user`, which fetches a user by UUID, it builds a `GetUserByIdentityResourceHandler` that looks a user up by its identity column, registers it in the module's `ConfigProvider` and `RoutesDelegator`, grants the route a permission, and covers it with functional tests.

## Our goal

Create a new endpoint that fetches a user record by its identity column.

We already have an endpoint that retrieves a user based on their UUID, so we can review it and create something similar.

## What we have

Let's print out all available endpoints:

```shell
php ./bin/cli.php route:list
```

This command lists every endpoint.
The rows we care about are the three under `/user/{id}`:

```text
+------+----------------+-------------------------------------+-------------------------------------+
|    # | Request method | Route name                          | Route path                          |
+------+----------------+-------------------------------------+-------------------------------------+
|   31 | DELETE         | user::delete-user                   | /user/{id}                          |
|   32 | GET            | user::view-user                     | /user/{id}                          |
|   33 | PATCH          | user::update-user                   | /user/{id}                          |
+------+----------------+-------------------------------------+-------------------------------------+
```

### Note

> **The above output is an excerpt.**
>
> More info about listing available endpoints can be found in [Displaying Dotkernel API endpoints](../commands/display-available-endpoints.md).

The endpoint we're focusing on is `user::view-user`, so let's take a closer look at its functionality.

If we search for the route name `user::view-user` we will find its definition in `src/User/src/RoutesDelegator.php`, where all user-related endpoints are declared:

```php
$routeCollector->group('/user/' . $id)
    ->delete('', DeleteUserResourceHandler::class, 'user::delete-user')
    ->get('', GetUserResourceHandler::class, 'user::view-user')
    ->patch('', PatchUserResourceHandler::class, 'user::update-user');
```

`$id` is `Core\App\ConfigProvider::REGEXP_UUID`, a placeholder constrained to the UUID format, so `/user/{id}` only matches a segment that is actually a UUID.

Our route points to `GetUserResourceHandler`, so let's navigate to it.

```php
class GetUserResourceHandler extends AbstractHandler
{
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(User::class)
        );
    }
}
```

The handler never queries the database.
`Api\App\Middleware\ResourceProviderMiddleware`, piped just before `DispatchMiddleware`, reads the `#[Resource]` attribute off the `handle()` method and does the lookup for it.
Three arguments of that attribute matter here:

| Argument | Default | Meaning |
| --- | --- | --- |
| `entity` | — | The entity class to load |
| `identifier` | `'id'` | The entity property to match on |
| `placeholder` | `'id'` | The route parameter holding the value |

With the defaults, the middleware calls `find()` on the `User` repository with the `id` route parameter.
It then places the entity on the request under the entity's class name, which is why the handler reads `$request->getAttribute(User::class)`.

The middleware also handles the failure cases, so no handler has to:

- No matching record throws `NotFoundException::create(Message::resourceNotFound('User'))`.
- A record whose `isDeleted()` returns `true` throws the same exception, so soft-deleted users are indistinguishable from absent ones.
- A `guard` argument, if given, decides whether the caller is allowed to see the entity at all.

We now have an understanding of how things work, and we can start to implement our own endpoint.

## Implementation

### Step 1: Create the handler

Create a new PHP class called `GetUserByIdentityResourceHandler.php` in the `src/User/src/Handler/User` folder.

```php
<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\User\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserByIdentityResourceHandler extends AbstractHandler
{
    #[Resource(entity: User::class, identifier: 'identity', placeholder: 'identity')]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(User::class)
        );
    }
}
```

The only difference from the existing handler is the attribute.
Because `identifier` is neither `id` nor `uuid`, the middleware switches from `find()` to `findOneBy(['identity' => ...])`.
`identity` is declared `unique: true` on the `User` entity, so that lookup can only ever return one row.

### Note

> The handler needs no constructor and no `#[Inject]` attribute.
> `HalResponseFactory` and `ResourceGenerator`, which `createResponse()` needs, are injected by `HandlerDelegatorFactory` in the next step, not through the constructor.

### Step 2: Register the handler

Go to `src/User/src/ConfigProvider.php` and add the handler in **two** places inside `getDependencies()`.

Under the `factories` key, so the container can build it:

```php
GetUserByIdentityResourceHandler::class => AttributedServiceFactory::class,
```

Under the `delegators` key, so it receives its response dependencies:

```php
GetUserByIdentityResourceHandler::class => [HandlerDelegatorFactory::class],
```

Both entries are required.
Every handler extending `AbstractHandler` starts with `$responseFactory` and `$resourceGenerator` set to `null`, and `HandlerDelegatorFactory` is what calls `setResponseFactory()` and `setResourceGenerator()` on it.
Register only the factory and the handler still resolves, but `$responseFactory` is never set and the first call to `createResponse()` fails.

### Step 3: Create the route

Next, declare the route in `src/User/src/RoutesDelegator.php`:

```php
$routeCollector->get('/user/{identity}', GetUserByIdentityResourceHandler::class, 'user::view-user-by-identity');
```

### Note

> Declare this route after the `'/user/' . $id` group.
>
> Static paths such as `/user/role` and `/user/account` are matched before any variable route, so they are never at risk.
> `/user/{id}` is, because its UUID constraint narrows what it accepts while `{identity}` accepts anything, and a UUID-shaped value matches both.
> The route declared first wins, so declaring ours last keeps UUIDs going to `user::view-user`.

### Step 4: Grant the route a permission

Go to `config/autoload/authorization.global.php` and add the route name under the `UserRoleEnum::Guest->value` key of the `permissions` array:

```php
UserRoleEnum::Guest->value => [
    // ...
    'user::view-user-by-identity',
],
```

Guest is the parent of User in the `roles` map, so granting it there lets everyone reach the endpoint, authenticated or not (for the sake of simplicity).
A request that carries no access token is given the guest identity by `AuthenticationMiddleware`, and `AuthorizationMiddleware` then checks the route name against that role's permissions.

### Note

> The response body is unaffected by which route served it.
> `MetadataMap` maps the `User` entity to `user::view-user`, so the HAL `_links.self` of a user fetched through `/user/{identity}` still points at `/user/{uuid}`.

## Writing tests

Because every new piece of code should be tested, we will write some tests for this endpoint also.

In the `test/Functional` folder create a new php class `IdentityTest.php`:

```php
<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use Core\App\Message;

use function json_decode;

class IdentityTest extends AbstractFunctionalTest
{
    public function testEmptyIdentityReturnsNotFound(): void
    {
        $response = $this->get('/user/');

        $this->assertResponseNotFound($response);
    }

    public function testInvalidIdentityReturnsNotFound(): void
    {
        $response = $this->get('/user/invalid_identity');

        $this->assertResponseNotFound($response);

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame(Message::resourceNotFound('User'), $data['detail']);
        $this->assertSame(404, $data['status']);
    }

    public function testValidIdentityReturnsUser(): void
    {
        $this->createUser([
            'identity' => 'valid_user',
        ]);

        $response = $this->get('/user/valid_user');

        $this->assertResponseOk($response);

        $user = json_decode($response->getBody()->getContents(), true);

        $this->assertSame('valid_user', $user['identity']);
    }
}
```

The two failure cases return the same status for different reasons.
`/user/` matches no route at all, because `{identity}` requires at least one character, so `ProblemDetailsNotFoundHandler` answers at the end of the pipeline.
`/user/invalid_identity` matches the route, reaches `ResourceProviderMiddleware`, finds no row and throws `NotFoundException`, which `ProblemDetailsMiddleware` renders:

```json
{
    "title": "Not Found",
    "type": "https://datatracker.ietf.org/doc/html/rfc9110#name-404-not-found",
    "status": 404,
    "detail": "User not found."
}
```

That is why the assertions read `detail` rather than a nested `error.messages` array.
See [Exceptions](../core-features/exceptions.md) for the full shape.

Planning and coding a new feature can be challenging at times, but reviewing our existing code or tutorials can serve as a source of inspiration.

## FAQ

**Q: How do I find the code behind an existing endpoint?**

A: List the routes with `php ./bin/cli.php route:list`, then search for the route name in the module's `RoutesDelegator.php` to find the handler it points to.
See [Displaying Dotkernel API endpoints](../commands/display-available-endpoints.md).

**Q: What are the steps to add an endpoint?**

A: Create the handler, register it in the module's `ConfigProvider` under both `factories` and `delegators`, declare the route in `RoutesDelegator.php`, and grant the route name a permission in `config/autoload/authorization.global.php`.

**Q: Why must the new route be declared last?**

A: Because `/user/{identity}` places no constraint on its placeholder, so it also matches a UUID.
Declaring it after `/user/{id}` keeps UUID lookups on the existing route.

**Q: Which factory do I register the handler with?**

A: `AttributedServiceFactory::class`, which resolves the dependencies declared by the handler's `#[Inject]` attribute — none, in this case.
The handler additionally needs `HandlerDelegatorFactory::class` under `delegators`.
See [Dependency injection](../core-features/dependency-injection.md).

**Q: Why doesn't the handler throw any exception?**

A: Because `ResourceProviderMiddleware` runs before it and throws `NotFoundException` when the identity matches no user.
The handler is only reached once the entity exists, so there is nothing left for it to reject.
See [Exceptions](../core-features/exceptions.md).

**Q: Do I need a new `MetadataMap` entry for the route?**

A: No.
`MetadataMap` maps an entity to the one route used to build its self link, and `User` is already mapped to `user::view-user`.

**Q: Why is the route added under `UserRoleEnum::Guest->value`?**

A: Only to keep the example simple — it lets everyone, including unauthenticated callers, view accounts.
Real deployments should grant it to the narrowest role that needs it.
See [Authorization](../core-features/authorization.md).

**Q: Will the endpoint work without an authorization entry?**

A: No.
A route with no permission granted to the caller's role is refused with `403 Forbidden`, even though the handler and route exist.

**Q: What should the tests cover?**

A: The three outcomes: an empty identity, an identity with no matching user, and a valid identity returning the expected record.

**Q: Where do functional tests live?**

A: In the `test/Functional` folder, extending `AbstractFunctionalTest`.
See [Test the installation](../installation/test-the-installation.md).
