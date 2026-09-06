# Injectable input filters

## Summary

Input filters are injected into handler constructors rather than instantiated inside `handle()`.
The page contrasts the old inline approach with the current one and shows how injection makes handlers easier to test, since the filter can be replaced with a mock.

## Details

In the current version of Dotkernel API has an Injectable Input Filter system into the constructors of our handlers.

When building APIs or backend applications in PHP, especially within frameworks that support dependency injection, input validation is a critical concern.
Many developers instinctively instantiate input filters or validators inside their handlers or controllers.
However, injecting input filters is a cleaner, more testable, and flexible approach.

The **previous** version that contained inline instantiation:

```php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $inputFilter = (new CreateAdminInputFilter())->setData((array) $request->getParsedBody());
    if (! $inputFilter->isValid()) {
        throw (new BadRequestException())->setMessages($inputFilter->getMessages());
    }

    $admin = $this->adminService->createAdmin($inputFilter->getValues());

    return $this->createdResponse($request, $admin);
}
```

While simple, this ties your handler directly to a concrete class.
It’s harder to reuse logic across contexts and mock or replace the filter during testing.

Our **current** approach uses constructor injection:

```php
class PostAdminResourceHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
        CreateAdminInputFilter::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
        protected CreateAdminInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $admin = $this->adminService->createAdmin((array) $this->inputFilter->getValues());

        return $this->createdResponse($request, $admin);
    }
}
```

This new approach makes it trivial to mock the filters during tests:

```php
$mockFilter = $this->createMock(CreateAdminInputFilter::class);
$mockFilter->method('setData')->willReturnSelf();
$mockFilter->method('isValid')->willReturn(true);

$handler = new PostAdminResourceHandler($adminService, $mockFilter);
$response = $handler->handle($request);
```

You're no longer tied to the real filter logic in your handler tests.

## FAQ

**Q: Why is injecting an input filter better than creating one in the handler?**

A: Inline instantiation couples the handler to a concrete filter class, which makes the logic harder to reuse and impossible to substitute in tests.
Injection moves that decision to the container.

**Q: How does the container know which filter to inject?**

A: From the `#[Inject]` attribute on the constructor, which lists the services to resolve in order.
See [Dependency injection](../core-features/dependency-injection.md).

**Q: Is an injected filter shared between requests?**

A: Each request gets a handler instance with its own filter, and `setData()` is called per request.
Do not rely on data set during an earlier call.

**Q: What happens when validation fails?**

A: The handler throws a `BadRequestException` carrying the filter's messages, which is rendered as a Problem Details response.
See [Problem details](problem-details.md).

**Q: Do I still call `setData()` and `isValid()` myself?**

A: Yes.
Injection only supplies the filter; populating it from the request body and checking validity remain the handler's job.

**Q: Can one handler use more than one input filter?**

A: Yes.
List each of them in the `#[Inject]` attribute and accept them as separate constructor parameters.
