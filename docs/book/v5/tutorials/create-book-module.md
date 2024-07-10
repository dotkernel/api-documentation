# Implementing a book module in Dotkernel API

## Folder and files structure

The below files structure is what we will have at the end of this tutorial and is just an example, you can have multiple components such as event listeners, wrappers, etc.

```markdown
.
└── src/
    └── Book/
        └── src/
            ├── Collection/
            │   └── BookCollection.php
            ├── Entity/
            │   └── Book.php
            ├── Handler/
            │   └── BookHandler.php
            ├── InputFilter/
            │   ├── Input/
            │   │   ├── AuthorInput.php
            │   │   ├── NameInput.php
            │   │   └── ReleaseDateInput.php
            │   └── BookInputFilter.php
            ├── Repository/
            │   └── BookRepository.php
            ├── Service/
            │   ├── BookService.php
            │   └── BookServiceInterface.php
            ├── ConfigProvider.php
            └── RoutesDelegator.php
```

* `src/Book/src/Collection/BookCollection.php` - a collection refers to a container for a group of related objects, typically used to manage sets of related entities fetched from a database
* `src/Book/src/Entity/Book.php` - an entity refers to a PHP class that represents a persistent object or data structure
* `src/Book/src/Handler/BookHandler.php` - handlers are middleware that can handle requests based on an action
* `src/Book/src/Repository/BookRepository.php` - a repository is a class responsible for querying and retrieving entities from the database
* `src/Book/src/Service/BookService.php` - is a class or component responsible for performing a specific task or providing functionality to other parts of the application
* `src/Book/src/ConfigProvider.php` -  is a class that provides configuration for various aspects of the framework or application
* `src/Book/src/RoutesDelegator.php` - a routes delegator is a delegator factory responsible for configuring routing middleware based on routing configuration provided by the application
* `src/Book/src/InputFilter/BookInputFilter.php` - input filters and validators
* `src/Book/src/InputFilter/Input/*` - input filters and validator configurations

## Creating and configuring the module

Firstly we will need the book module, so we will implement and create the basics for a module to be registered and functional.

In `src` folder we will create the `Book` folder and in this we will create the `src` folder. So the final structure will be like this: `src/Book/src`.

In `src/Book/src` we will create 2 php files: `RoutesDelegator.php` and `ConfigProvider.php`. This files will be updated later with all needed configuration.

* `src/Book/src/RoutesDelegator.php`

```php
<?php

namespace Api\Book;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();
        
        return $app;
    }
}
```

* `src/Book/src/ConfigProvider.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book;

use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
             MetadataMap::class => $this->getHalConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class
                ]
            ],
            'factories' => [
            ],
            'aliases'   => [
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            
        ];
    }

    private function getHalConfig(): array
    {
        return [
           
        ];
    }

}
```

### Registering the module

* register the module config by adding the `Api\Book\ConfigProvider::class` in `config/config.php` under the `Api\User\ConfigProvider::class`
* register the namespace by adding this line `"Api\\Book\\": "src/Book/src/"`, in composer.json under the autoload.psr-4 key
* update Composer autoloader by running the command:

```shell
composer dump-autoload
```

That's it. The module is now registered and, we can continue creating Handlers, Services, Repositories and whatever is needed for out tutorial.

## File creation and contents

Each file below have a summary description above of what that file does.

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

* `src/Book/src/Entity/Book.php`

To keep things simple in this tutorial our book will have 3 properties: `name`, `author` and `release date`.

```php
<?php

declare(strict_types=1);

namespace Api\Book\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\TimestampsTrait;
use Api\Book\Repository\BookRepository;
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
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
            'author' => $this->getAuthor(),
            'releaseDate' => $this->getReleaseDate(),
        ];
    }
}

```

* `src/Book/src/Repository/BookRepository.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Repository;

use Api\App\Helper\PaginationHelper;
use Api\Book\Collection\BookCollection;
use Api\Book\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
 #[Entity(name: Book::class)]
class BookRepository extends EntityRepository
{
    public function saveBook(Book $book): Book
    {
        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();

        return $book;
    }

    public function getBooks(array $filters = []): BookCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('book')
            ->from(Book::class, 'book')
            ->orderBy($filters['order'] ?? 'book.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        return new BookCollection($qb, false);
    }
}
```

* `src/Book/src/Service/BookServiceInterface.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Service;

use Api\Book\Repository\BookRepository;

interface BookServiceInterface
{
    public function getRepository(): BookRepository;
}
```

* `src/Book/src/Service/BookService.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Service;

use Api\Book\Entity\Book;
use Api\Book\Repository\BookRepository;
use Dot\DependencyInjection\Attribute\Inject;
use DateTimeImmutable;

class BookService implements BookServiceInterface
{
    #[Inject(BookRepository::class)]
    public function __construct(protected BookRepository $bookRepository)
    {
    }

    public function getRepository(): BookRepository
    {
        return $this->bookRepository;
    }

    public function createBook(array $data): Book
    {
        $book = new Book(
            $data['name'],
            $data['author'],
            new DateTimeImmutable($data['releaseDate'])
        );

        return $this->bookRepository->saveBook($book);
    }

    public function getBooks(array $filters = [])
    {
        return $this->bookRepository->getBooks($filters);
    }
}
```

When creating or updating a book, we will need some validators, so we will create input filters that will be used to validate the data received in the request

* `src/Book/src/InputFilter/Input/AuthorInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
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
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'author'),
            ], true);
    }
}
```

* `src/Book/src/InputFilter/Input/NameInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
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
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'name'),
            ], true);
    }
}
```

* `src/Book/src/InputFilter/Input/ReleaseDateInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\Date;
use Laminas\Validator\NotEmpty;

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
                'message' => sprintf(Message::INVALID_VALUE, 'releaseDate'),
            ], true);
    }
}
```

Now we add all the inputs together in a parent input filter.

* `src/Book/src/InputFilter/BookInputFilter.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter;

use Api\Book\InputFilter\Input\AuthorInput;
use Api\Book\InputFilter\Input\NameInput;
use Api\Book\InputFilter\Input\ReleaseDateInput;
use Laminas\InputFilter\InputFilter;

class BookInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new NameInput('name'));
        $this->add(new AuthorInput('author'));
        $this->add(new ReleaseDateInput('releaseDate'));
    }
}
```

We split all the inputs just for the purpose of this tutorial and to demonstrate a clean `BookInputFiler` but you could have all the inputs created directly in the `BookInputFilter` like this:

```php
$nameInput = new Input();
$nameInput->setRequired(true);

$nameInput->getFilterChain()
    ->attachByName(StringTrim::class)
    ->attachByName(StripTags::class);

$nameInput->getValidatorChain()
    ->attachByName(NotEmpty::class, [
        'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'name'),
    ], true);

$this->add($nameInput);
```

Now it's time to create the handler.

* `src/Book/src/Handler/BookHandler.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book\Handler;

use Api\App\Handler\HandlerTrait;
use Api\Book\InputFilter\BookInputFilter;
use Api\Book\Service\BookServiceInterface;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dot\DependencyInjection\Attribute\Inject;

class BookHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    #[Inject(
        HalResponseFactory::class,
        ResourceGenerator::class,
        BookServiceInterface::class,
        "config"
    )]
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected BookServiceInterface $bookService,
        protected array $config
    ) {
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $book = $this->bookService->getRepository()->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        if (! $book instanceof Book){
            return $this->notFoundResponse();
        }

        return $this->createResponse($request, $book);
    }

    public function getCollection(ServerRequestInterface $request): ResponseInterface
    {
        $books = $this->bookService->getBooks($request->getQueryParams());

        return $this->createResponse($request, $books);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new BookInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages(), StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
        }

        $book = $this->bookService->createBook($inputFilter->getValues());

        return $this->createResponse($request, $book);
    }
}

```

After we have the handler, we need to register some routes in the `RoutesDelegator`, the same we created when  we registered the module.

* `src/Book/src/RoutesDelegator.php`

```php
<?php

namespace Api\Book;

use Api\Book\Handler\BookHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $uuid = \Api\App\RoutesDelegator::REGEXP_UUID;

        $app->get(
            '/books',
            BookHandler::class,
            'books.list'
        );

        $app->get(
            '/book/'.$uuid,
            BookHandler::class,
            'book.show'
        );

        $app->post(
            '/book',
            BookHandler::class,
            'book.create'
        );

        return $app;
    }
}
```

We need to configure access to the newly created endpoints, add `books.list`, `book.show` and `book.create` to the authorization rbac array, under the `UserRole::ROLE_GUEST` key.
> Make sure you read and understand the rbac documentation.

It's time to update the `ConfigProvider` with all the necessary configuration needed, so the above files to work properly like dependency injection, aliases, doctrine mapping and so on.

* `src/Book/src/ConfigProvider.php`

```php
<?php

declare(strict_types=1);

namespace Api\Book;

use Api\Book\Collection\BookCollection;
use Api\Book\Entity\Book;
use Api\Book\Handler\BookHandler;
use Api\Book\Repository\BookRepository;
use Api\Book\Service\BookService;
use Api\Book\Service\BookServiceInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;
use Api\App\ConfigProvider as AppConfigProvider;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class
                ]
            ],
            'factories' => [
                BookHandler::class    => AttributedServiceFactory::class,
                BookService::class    => AttributedServiceFactory::class,
                BookRepository::class => AttributedRepositoryFactory::class,
            ],
            'aliases'   => [
                BookServiceInterface::class     => BookService::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'   => [
                    'drivers' => [
                        'Api\Book\Entity' => 'BookEntities'
                    ],
                ],
                'BookEntities'  => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Entity',
                ],
            ],
        ];
    }

    private function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(BookCollection::class, 'books.list', 'books'),
            AppConfigProvider::getResource(Book::class, 'book.show')
        ];
    }

}
```

## Migrations

We created the `Book` entity, but we didn't create the associated table for it.

> You can check the mapping files by running:

```shel
php bin/doctrine orm:validate-schema
```

Doctrine can handle the table creation, run the following command:

```shell
vendor/bin/doctrine-migrations diff --filter-expression='/^(?!oauth_)/'
```

This will check for differences between your entities and database structure and create migration files if necessary, in `data/doctrine/migrations`.

To execute the migrations run:

```shell
vendor/bin/doctrine-migrations migrate
```

## Checking endpoints

If we did everything as planned we can call the `http://0.0.0.0:8080/book` endpoint and create a new book:

```shell
curl -X POST http://0.0.0.0:8080/book
  -H "Content-Type: application/json"
  -d '{"name": "test", "author": "author name", "releaseDate": "2023-03-03"}'
```

To list the books use:

```shell
curl http://0.0.0.0:8080/books
```

To retrieve a book use:

```shell
curl http://0.0.0.0:8080/book/{uuid}
```
