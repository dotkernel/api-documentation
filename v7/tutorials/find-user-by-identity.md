# A practical example: Find a user by identity

## Summary

A worked example of adding an endpoint by following an existing one.
Starting from `user.view`, which fetches a user by UUID, it builds an `IdentityHandler` that looks a user up by identity, registers it in the module's `ConfigProvider` and `RoutesDelegator`, grants the route a permission, and covers it with functional tests.

## Our goal

Create a new endpoint that fetches a user record by its identity column.

We already have an endpoint that retrieves a user based on their UUID, so we can review it and create something similar.

## What we have

Let's print out all available endpoints using :

```shell
php ./bin/cli.php route:list
```

This command will list all available endpoints, which looks like this:

```text
+--------+---------------------------------+--------------------------------+
| Method | Name                            | Path                           |
+--------+---------------------------------+--------------------------------+
| POST   | account.activate.request        | /account/activate              |
| PATCH  | account.activate                | /account/activate/{hash}       |
| PATCH  | account.modify-password         | /account/reset-password/{hash} |
.............................................................................
.............................................................................
.............................................................................
| GET    | user.my-avatar.view             | /user/my-avatar                |
| GET    | user.role.list                  | /user/role                     |
| GET    | user.role.view                  | /user/role/{id}                |
| PATCH  | user.update                     | /user/{id}                     |
| GET    | user.view                       | /user/{id}                     |
+--------+---------------------------------+--------------------------------+
```

### Note

> **The above output is just an example.**
>
> More info about listing available endpoints can be found in `../commands/display-available-endpoints.md`.

The endpoint we're focusing on is the last one, `user.view`, so let's take a closer look at its functionality.

If we search for the route name `user.view` we will find its definition in the `src/User/src/RoutesDelegator.php` class, where all user-related endpoints are found.

```php
$app->get('/user/' . $id, UserHandler::class, 'user.view');
```

Our route points to `get` method from `UserHandler` so let's navigate to that method.

```php
public function get(ServerRequestInterface $request): ResponseInterface
{
    $user = $this->userService->findOneBy(['id' => $request->getAttribute('id')]);

    return $this->createResponse($request, $user);
}
```

As we can see, the method will query the database for the user based on its id taken from the endpoint.

We now have an understanding of how things work, and we can start to implement our own endpoint.

### Implementation

We need to create a new handler that will process our request, we can call it `IdentityHandler`.

Create a new PHP class called `IdentityHandler.php` in `src/User/src/Handler` folder.

```php
<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class IdentityHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    #[Inject(
        HalResponseFactory::class,
        ResourceGenerator::class,
        UserServiceInterface::class,
    )]
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $identity = $request->getAttribute('identity');
        if (empty($identity)) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'identity')]);
        }

        $user = $this->userService->findByIdentity($identity);
        if (! $user instanceof User) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        return $this->createResponse($request, $user);
    }
}
```

Our handler is very similar to the existing one, with some extra steps:

* We store the identity from the request in the `$identity` variable for later use.
* If the identity is empty we throw a `BadRequestException` with an appropriate message.
* If we can't find the user in the database, we throw an `NotFoundException`.
* If the record is found, we generate and return the response.

The next step is to register the new handler.
To do this, go to `src/User/src/ConfigProvider.php`.
In the `getDependencies()` method under the `factories` key add `IdentityHandler::class => AttributedServiceFactory::class,`

Next, create the route in `src/User/src/RoutesDelegator.php`:

```php
    $app->get(
        '/user/{identity}',
        IdentityHandler::class,
        'user.view.identity'
    );
```

### Note

> Make sure to register the endpoint as the last one to not shadow existing endpoints.

The last step is to set permissions on the newly created route.

Go to `config/autoload/authorization.global.php` and add our route name (`user.view.identity`) under the `UserRole::ROLE_GUEST` key.
This will give access to every user, including guests, to view other accounts (for the sake of simplicity).

### Writing tests

Because every new piece of code should be tested, we will write some tests for this endpoint also.

In the `test/Functional` folder create a new php class `IdentityTest.php`:

```php
<?php

namespace ApiTest\Functional;

use Api\App\Message;

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
        $messages = json_decode($response->getBody()->getContents(), true);

        $this->assertResponseNotFound($response);
        $this->assertNotEmpty($messages);
        $this->assertIsArray($messages);
        $this->assertNotEmpty($messages['error']['messages'][0]);
        $this->assertIsString($messages['error']['messages'][0]);
        $this->assertSame(Message::USER_NOT_FOUND, $messages['error']['messages'][0]);
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

Planning and coding a new feature can be challenging at times, but reviewing our existing code or tutorials can serve as a source of inspiration.

## FAQ

**Q: How do I find the code behind an existing endpoint?**

A: List the routes with `php ./bin/cli.php route:list`, then search for the route name in the module's `RoutesDelegator.php` to find the handler it points to.
See [Displaying Dotkernel API endpoints](../commands/display-available-endpoints.md).

**Q: What are the steps to add an endpoint?**

A: Create the handler, register it in the module's `ConfigProvider` under `factories`, declare the route in `RoutesDelegator.php`, and grant the route name a permission in `config/autoload/authorization.global.php`.

**Q: Why must the new route be registered last?**

A: Because `/user/{identity}` and `/user/{id}` match the same shape.
Registering the new route last stops it from shadowing the existing ones.

**Q: Which factory do I register the handler with?**

A: `AttributedServiceFactory::class`, which resolves the dependencies declared by the handler's `#[Inject]` attribute.
See [Dependency injection](../core-features/dependency-injection.md).

**Q: Why does the handler throw two different exceptions?**

A: `BadRequestException` covers a missing identity in the request (a client error in the input), while `NotFoundException` covers a valid identity with no matching record.
They map to 400 and 404 respectively.
See [Exceptions](../core-features/exceptions.md).

**Q: Why is the route added under `UserRole::ROLE_GUEST`?**

A: Only to keep the example simple — it lets everyone, including guests, view accounts.
Real deployments should grant it to the narrowest role that needs it.
See [Authorization](../core-features/authorization.md).

**Q: Will the endpoint work without an authorization entry?**

A: No. A route with no permission granted to the caller's role is refused, even though the handler and route exist.

**Q: What should the tests cover?**

A: The three outcomes: an empty identity, an identity with no matching user, and a valid identity returning the expected record.

**Q: Where do functional tests live?**

A: In the `test/Functional` folder, extending `AbstractFunctionalTest`.
See [Test the installation](../installation/test-the-installation.md).
