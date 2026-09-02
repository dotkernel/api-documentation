# Dependency Injection

## Summary

Dotkernel API resolves dependencies through the `dot-dependency-injection` package, which supports constructor injection only.
You declare the services a class needs with the `#[Inject]` attribute on its constructor, then register the class in a `ConfigProvider` using `AttributedServiceFactory`.

## Details

Dependency injection is a design pattern used in software development to implement inversion of control.
In simpler terms, it's the act of providing dependencies for an object during instantiation.

In PHP, dependency injection can be implemented in various ways, including through constructor injection, setter injection and property injection.

> Introduced in Dotkernel API 5.0.0

Dotkernel API, through its [dot-dependency-injection](https://github.com/dotkernel/dot-dependency-injection) package, focuses only on constructor injection.

## Usage

**Dotkernel API** comes out of the box with the [dot-dependency-injection](https://github.com/dotkernel/dot-dependency-injection) package, which provides all we need for injecting dependencies into any object you want.

`dot-dependency-injection` determines the dependencies by looking at the `#[Inject]` attribute, added to the constructor of a class.
Dependencies are specified as separate parameters of the `#[Inject]` attribute.

For our example we will inject `UserService` and `config` dependencies into a `UseHandler`.

```php
use Dot\DependencyInjection\Attribute\Inject;

class UserHandler implements RequestHandlerInterface
{
    #[Inject(
        UserService::class,
        "config",
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected array $config,
    ) {
    }
}
```

> If your class needs the value of a specific configuration key, you can specify the path using dot notation `config.example`.

The next step is to register the class in the `ConfigProvider` under `factories` using `Dot\DependencyInjection\Factory\AttributedServiceFactory::class`

```php
public function getDependencies(): array
{
    return [
        'factories' => [
            UserHandler::class => AttributedServiceFactory::class
        ]
    ];
}
```

That's it.
When your object is instantiated from the container, it will automatically have its dependencies resolved.

> Dependency injection is available to any object within Dotkernel API.
> For example, you can inject dependencies in a service, a handler and so on, simply by registering it in the `ConfigProvider`.

## FAQ

**Q: Which injection styles are supported?**

A: Constructor injection only.
`dot-dependency-injection` deliberately leaves out setter and property injection.

**Q: What are the two steps to make a class injectable?**

A: Add the `#[Inject]` attribute to its constructor listing the dependencies in order, then register the class in the `ConfigProvider` under `factories` with `AttributedServiceFactory::class`.

**Q: Must the `#[Inject]` arguments match the constructor parameters?**

A: Yes, in the same order.
Each entry in the attribute maps to the parameter at that position.

**Q: How do I inject configuration instead of a service?**

A: Pass `"config"` to receive the whole configuration array, or use dot notation such as `config.example` to receive a single key's value.

**Q: Can I inject an interface rather than a concrete class?**

A: Yes, provided the interface is registered in the container and resolves to an implementation.
Typing the parameter against the interface keeps your class decoupled from the implementation.

**Q: Which classes can use this?**

A: Any object in Dotkernel API — handlers, services, filters and others — as long as it is registered in a `ConfigProvider`.

**Q: Since which version is this available?**

A: Dotkernel API 5.0.0.
