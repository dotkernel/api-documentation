# Dependency Injection

Dependency Injection is a design pattern used in software development to implement inversion of control or in simple
terms is the act of providing dependencies for an object during instantiation.

In PHP, dependency injection can be implemented in various ways, including through constructor injection,
setter injection, and property injection. 

DotKernel API, through it's
[dot-dependency-injection](https://github.com/dotkernel/dot-dependency-injection) package focuses only on constructor
injection.

## Usage
DotKernel API comes out of the box with [dot-dependency-injection](https://github.com/dotkernel/dot-dependency-injection)
package, which provide all we need for injecting dependencies in any object you want.

`dot-dependency-injection` determines the dependencies by looking at the `#[Inject]` attribute,
added to the constructor of a class. Dependencies are specified as separate parameters of the `#[Inject]`
attribute.

For our example we will inject a `UserService` and `config` dependencies in a `UseHandler`.

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

>If your class needs the value of a specific configuration key, you can specify the path using dot notation: `config.example`

After register the class in the `ConfigProvider`, under `factories`, using `Dot\DependencyInjection\Factory\AttributedServiceFactory::class`

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

That's it, by registering this, when your object will instantiate from the container, it will automatically resolve
the dependencies needed for you object.

>Dependencies injection applies to any object within DotKernel API, for example, you could inject dependencies in
> a service and so on, just need to register it in the `ConfigProvider`
