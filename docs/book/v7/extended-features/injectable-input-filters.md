# Injectable input filters

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

While simple, this ties your handler directly to a concrete class. It’s harder to reuse logic across contexts and mock or replace the filter during testing.

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
