# Implementing a book module in Dotkernel API

## Folder and files structure

The below files structure is what we will have at the end of this tutorial and is just an example; you can have multiple components such as event listeners, wrappers, etc.

```markdown
.
└── src/
    ├── Book/
    │   └── src/
    │       ├── Collection/
    │       │   └── BookCollection.php
    │       ├── Handler/
    │       │   ├── GetBookCollectionHandler.php
    │       │   ├── GetBookResourceHandler.php
    │       │   └── PostBookResourceHandler.php
    │       ├── InputFilter/
    │       │   ├── Input/
    │       │   │   ├── AuthorInput.php
    │       │   │   ├── NameInput.php
    │       │   │   └── ReleaseDateInput.php
    │       │   └── CreateBookInputFilter.php
    │       ├── Service/
    │       │   ├── BookService.php
    │       │   └── BookServiceInterface.php
    │       ├── ConfigProvider.php
    │       └── RoutesDelegator.php
    └── Core/
        └── src/
            └── Book/
                └── src/
                    ├──Entity/
                    │   └──Book.php
                    ├──Repository/
                    │   └──BookRepository.php
                    └── ConfigProvider.php
```

* `src/Book/src/Collection/BookCollection.php` – a collection refers to a container for a group of related objects, typically used to manage sets of related entities fetched from a database
* `src/Book/src/Handler/GetBookCollectionHandler.php` – handler that reflects the GET action for the BookCollection class
* `src/Book/src/Handler/GetBookResourceHandler.php` – handler that reflects the GET action for the Book entity
* `src/Book/src/Handler/PostBookResourceHandler.php` – handler that reflects the POST action for the Book entity
* `src/Book/src/InputFilter/Input/*` – input filters and validator configurations
* `src/Book/src/InputFilter/CreateBookInputFilter.php` – input filters and validators
* `src/Book/src/Service/BookService.php` – is a class or component responsible for performing a specific task or providing functionality to other parts of the application
* `src/Book/src/Service/BookServiceInterface.php` – interface that reflects the publicly available methods in `BookService`
* `src/Book/src/ConfigProvider.php` – is a class that provides configuration for various aspects of the framework or application
* `src/Book/src/RoutesDelegator.php` – a RoutesDelegator is a delegator factory responsible for configuring routing middleware based on routing configuration provided by the application
* `src/Core/src/Book/src/Entity/Book.php` – an entity refers to a PHP class that represents a persistent object or data structure
* `src/Core/src/Book/src/Repository/BookRepository.php` – a repository is a class responsible for querying and retrieving entities from the database
* `src/Core/src/Book/src/ConfigProvider.php` – is a class that provides configuration for Doctrine ORM

## File creation and contents

In `src` and `src/Core/src` folders we will create one `Book` folder and in those we will create the `src` folder.
So the final structure will be like this: `src/Book/src` and `src/Core/src/Book/src`.

* `src/Book/src/Collection/BookCollection.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Collection;

use Api\App\Collection\ResourceCollection;

class BookCollection extends ResourceCollection
{
}

```

* `src/Core/src/Book/src/Entity/Book.php`

To keep things simple in this tutorial, our book will have three properties: `name`, `author` and `releaseDate`.

```php
<?php

declare(strict_types=1);

namespace Core\Book\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\Book\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table("book")]
#[ORM\HasLifecycleCallbacks]
class Book extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\Column(name: "name", type: "string", length: 100)]
    protected string $name;

    #[ORM\Column(name: "author", type: "string", length: 100)]
    protected string $author;

    #[ORM\Column(name: "releaseDate", type: "datetime_immutable")]
    protected DateTimeImmutable $releaseDate;

    public function __construct(string $name, string $author, DateTimeImmutable $releaseDate)
    {
        parent::__construct();

        $this->setName($name);
        $this->setAuthor($author);
        $this->setReleaseDate($releaseDate);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(DateTimeImmutable $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'        => $this->getUuid()->toString(),
            'name'        => $this->getName(),
            'author'      => $this->getAuthor(),
            'releaseDate' => $this->getReleaseDate(),
        ];
    }
}

```

* `src/Core/src/Book/src/Repository/BookRepository.php`

```php
<?php

declare(strict_types=1);

namespace Core\Book\Repository;

use Core\App\Repository\AbstractRepository;
use Core\Book\Entity\Book;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: Book::class)]
class BookRepository extends AbstractRepository
{
    public function getBooks(array $params, array $filters = []): QueryBuilder
    {
        return $this
            ->getQueryBuilder()
            ->select('book')
            ->from(Book::class, 'book')
            ->orderBy($params['sort'], $params['dir'])
            ->setFirstResult($params['offset'])
            ->setMaxResults($params['limit']);
    }
}

```

* `src/Book/src/Service/BookServiceInterface.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Service;

use Core\Book\Entity\Book;
use Core\Book\Repository\BookRepository;
use Doctrine\ORM\QueryBuilder;

interface BookServiceInterface
{
    public function getBookRepository(): BookRepository;

    public function saveBook(array $data): Book;

    public function getBooks(array $params = []): QueryBuilder;
}

```

* `src/Book/src/Service/BookService.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Service;

use Core\App\Helper\Paginator;
use Core\Book\Entity\Book;
use Core\Book\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Inject;
use Exception;

use function in_array;

class BookService implements BookServiceInterface
{
    #[Inject(BookRepository::class)]
    public function __construct(
        protected BookRepository $bookRepository
    ) {
    }

    public function getBookRepository(): BookRepository
    {
        return $this->bookRepository;
    }

    /**
     * @throws Exception
     */
    public function saveBook(array $data): Book
    {
        $book = new Book(
            $data['name'],
            $data['author'],
            new DateTimeImmutable($data['releaseDate'])
        );

        $this->bookRepository->saveResource($book);

        return $book;
    }

    public function getBooks(array $params = []): QueryBuilder
    {
        $filters = $params['filters'] ?? [];
        $params  = Paginator::getParams($filters, 'book.created');

        $sortableColumns = [
            'book.name',
            'book.author',
            'book.releaseDate',
            'book.created',
        ];

        if (! in_array($params['sort'], $sortableColumns, true)) {
            $params['sort'] = 'book.created';
        }

        return $this->bookRepository->getBooks($params, $filters);
    }
}

```

When creating or updating a book, we will need some validators, so we will create input filters that will be used to validate the data received in the request

* `src/Book/src/InputFilter/Input/AuthorInput.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;

class AuthorInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true);
    }
}

```

* `src/Book/src/InputFilter/Input/NameInput.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;

class NameInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true);
    }
}

```

* `src/Book/src/InputFilter/Input/ReleaseDateInput.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\Date;

class ReleaseDateInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(Date::class, [
                'message' => Message::invalidValue('releaseDate'),
            ], true);
    }
}

```

Now we add all the inputs together in a parent input filter.

* `src/Book/src/InputFilter/CreateBookInputFilter.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\InputFilter;

use Api\Book\InputFilter\Input\AuthorInput;
use Api\Book\InputFilter\Input\NameInput;
use Api\Book\InputFilter\Input\ReleaseDateInput;
use Core\App\InputFilter\AbstractInputFilter;

class CreateBookInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new NameInput('name'));
        $this->add(new AuthorInput('author'));
        $this->add(new ReleaseDateInput('releaseDate'));
    }
}

```

We create separate `Input` files to demonstrate their reusability and obtain a clean `CreateBookInputFilter` but you could have all the inputs created directly in the `CreateBookInputFilter` like this:

```php
$nameInput = new Input('name');
$nameInput->setRequired(true);

$nameInput->getFilterChain()
    ->attachByName(StringTrim::class)
    ->attachByName(StripTags::class);

$nameInput->getValidatorChain()
    ->attachByName(NotEmpty::class, [
        'message' => Message::VALIDATOR_REQUIRED_FIELD,
    ], true);

$this->add($nameInput);

$authorInput = new Input('author');
$authorInput->setRequired(true);

$authorInput->getFilterChain()
    ->attachByName(StringTrim::class)
    ->attachByName(StripTags::class);

$authorInput->getValidatorChain()
    ->attachByName(NotEmpty::class, [
        'message' => Message::VALIDATOR_REQUIRED_FIELD,
    ], true);

$this->add($authorInput);

$releaseDateInput = new Input('releaseDate');
$releaseDateInput->setRequired(true);

$releaseDateInput->getFilterChain()
    ->attachByName(StringTrim::class)
    ->attachByName(StripTags::class);

$releaseDateInput->getValidatorChain()
    ->attachByName(NotEmpty::class, [
        'message' => Message::VALIDATOR_REQUIRED_FIELD,
    ], true);

$this->add($releaseDateInput);
```

Now it's time to create the handlers.

* `src/Book/src/Handler/GetBookCollectionHandler.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Handler;

use Api\App\Handler\AbstractHandler;
use Api\Book\Collection\BookCollection;
use Api\Book\Service\BookServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetBookCollectionHandler extends AbstractHandler
{
    #[Inject(
        BookServiceInterface::class,
    )]
    public function __construct(
        protected BookServiceInterface $bookService,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new BookCollection($this->bookService->getBooks($request->getQueryParams()))
        );
    }
}

```

* `src/Book/src/Handler/GetBookResourceHandler.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Handler;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\Book\Entity\Book;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetBookResourceHandler extends AbstractHandler
{
    #[Resource(entity: Book::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(Book::class)
        );
    }
}

```

* `src/Book/src/Handler/PostBookResourceHandler.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\Book\InputFilter\CreateBookInputFilter;
use Api\Book\Service\BookServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostBookResourceHandler extends AbstractHandler
{
    #[Inject(
        CreateBookInputFilter::class,
        BookServiceInterface::class,
    )]
    public function __construct(
        protected CreateBookInputFilter $inputFilter,
        protected BookServiceInterface $bookService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => $this->inputFilter->getMessages()]
            );
        }

        /** @var non-empty-array<non-empty-string, mixed> $data */
        $data = (array) $this->inputFilter->getValues();

        return $this->createdResponse($request, $this->bookService->saveBook($data));
    }
}

```

In `src/Book/src` we now create the two PHP files: `RoutesDelegator.php` and `ConfigProvider.php`.

`RoutesDelegator.php` contains all of our routes while `ConfigProvider` contains all the necessary configuration needed, so the above files work properly like dependency injection, aliases and so on.

* `src/Book/src/ConfigProvider.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book;

use Api\App\ConfigProvider as AppConfigProvider;
use Api\App\Factory\HandlerDelegatorFactory;
use Api\Book\Collection\BookCollection;
use Api\Book\Handler\GetBookCollectionHandler;
use Api\Book\Handler\GetBookResourceHandler;
use Api\Book\Handler\PostBookResourceHandler;
use Api\Book\Service\BookService;
use Api\Book\Service\BookServiceInterface;
use Core\Book\Entity\Book;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class              => [RoutesDelegator::class],
                PostBookResourceHandler::class  => [HandlerDelegatorFactory::class],
                GetBookResourceHandler::class   => [HandlerDelegatorFactory::class],
                GetBookCollectionHandler::class => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                PostBookResourceHandler::class  => AttributedServiceFactory::class,
                GetBookResourceHandler::class   => AttributedServiceFactory::class,
                GetBookCollectionHandler::class => AttributedServiceFactory::class,
                BookService::class              => AttributedServiceFactory::class,
            ],
            'aliases'    => [
                BookServiceInterface::class => BookService::class,
            ],
        ];
    }

    private function getHalConfig(): array
    {
        return [
            AppConfigProvider::getResource(Book::class, 'book::view-book'),
            AppConfigProvider::getCollection(BookCollection::class, 'book::list-books', 'books'),
        ];
    }
}

```

* `src/Book/src/RoutesDelegator.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book;

use Api\Book\Handler\GetBookCollectionHandler;
use Api\Book\Handler\GetBookResourceHandler;
use Api\Book\Handler\PostBookResourceHandler;
use Core\App\ConfigProvider;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $uuid = ConfigProvider::REGEXP_UUID;

        /** @var RouteCollectorInterface $routeCollector */
        $routeCollector = $container->get(RouteCollectorInterface::class);

        $routeCollector->post('/book', PostBookResourceHandler::class, 'book::create-book');
        $routeCollector->get('/book/' . $uuid, GetBookResourceHandler::class, 'book::view-book');
        $routeCollector->get('/book', GetBookCollectionHandler::class, 'book::list-books');

        return $callback();
    }
}

```

In `src/Core/src/Book/src` we will create `ConfigProvider.php` where we configure Doctrine ORM.

* `src/Core/src/Book/src/ConfigProvider.php`.

```php
<?php

declare(strict_types=1);

namespace Core\Book;

use Core\Book\Repository\BookRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories' => [
                BookRepository::class => AttributedRepositoryFactory::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'  => [
                    'drivers' => [
                        'Core\Book\Entity' => 'BookEntities',
                    ],
                ],
                'BookEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
    }
}

```

### Registering the module

* register the module config by adding `Api\Book\ConfigProvider::class,` and `Core\Book\ConfigProvider::class,` in `config/config.php` under the `Api\User\ConfigProvider::class,`
* register the namespace by adding this line `"Api\\Book\\": "src/Book/src/"` and `"Core\\Book\\": "src/Core/src/Book/src/"`, in `composer.json` under the `autoload`.`psr-4` key
* update Composer autoloader by running the command:

```shell
composer dump-autoload
```

That's it. The module is now registered.

We need to configure access to the newly created endpoints.
Open `config/autoload/authorization.global.php` and append the below route names to the `UserRoleEnum::Guest->value` key:

* `book::list-books`
* `book::view-book`
* `book::create-book`

> Make sure you read and understand the rbac [documentation](https://docs.dotkernel.org/dot-rbac-guard/v4/configuration/).

## Migrations

We created the `Book` entity, but we didn't create the associated table for it.

> You can check the mapping files by running:

```shell
php ./bin/doctrine orm:validate-schema
```

Doctrine can handle the table creation, run the following command:

```shell
php ./vendor/bin/doctrine-migrations diff
```

This will check for differences between your entities and database structure and create migration files if necessary, in `src/Core/src/App/src/Migration`.

To execute the migrations, run:

```shell
php ./vendor/bin/doctrine-migrations migrate
```

## Checking endpoints

First, we start a local server by executing:

```shell
composer serve
```

If we did everything as planned, we should be able to create a new book by executing the below command:

```shell
curl -X POST http://0.0.0.0:8080/book
  -H "Content-Type: application/json"
  -d '{"name": "test", "author": "author name", "releaseDate": "2023-03-03"}'
```

To list the books, use:

```shell
curl http://0.0.0.0:8080/book
```

To fetch a book, `curl` one of the links found in the output of the **list books** command, under `_embedded` . `books` . * . `_links` . `self` . `href`.

The link should have the following format:

```shell
curl http://0.0.0.0:8080/book/{uuid}
```
