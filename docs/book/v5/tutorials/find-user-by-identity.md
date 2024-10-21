# Find user by identity - A practical example

## Our goal

Create a new endpoint that fetches a user based on its identity.

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
| GET    | user.role.view                  | /user/role/{uuid}              |
| PATCH  | user.update                     | /user/{uuid}                   |
| GET    | user.view                       | /user/{uuid}                   |
+--------+---------------------------------+--------------------------------+
```

### Note

> **The above output is just an example.**
> 
> More info about listing available endpoints can be found [here](../commands/display-available-endpoints).

The endpoint we're focusing on is the last one, `user.view`, so let's take a closer look at its functionality.

If we search for the route name `user.view` we will find its definition in the `src/User/src/RoutesDelegator.php` class, here live all user related endpoints.

```php
$app->get('/user/' . $uuid, UserHandler::class, 'user.view');
```

Our route points to `get` method from `UserHandler` so let's navigate to that method.

```php
public function get(ServerRequestInterface $request): ResponseInterface
{
    $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

    return $this->createResponse($request, $user);
}
```

As we can see, the method will query the database for the user based on its uuid taken from the endpoint.

We now have an understanding of how things work and we can start to implement our own endpoint.

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
* If we can't find the user in the database we throw an `NotFoundException`.
* We generate and return the response.

The next step is to register the new handler,
to do this go to `src/User/src/ConfigProvider.php`
and in the `getDependencies()` method under the `factories` key add `IdentityHandler::class => AttributedServiceFactory::class,`

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

Go to `config/autoload/authorization.global.php` and add our route name (`user.view.identity`) under the `UserRole::ROLE_GUEST` key
This will give access to every user, including guests to view other accounts. (for the sake of simplicity)


### Writing tests

Because every new piece of code should be tested we will write some tests for this endpoint also.

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