# Implementing a book module in Dotkernel API using DotMaker

The `dotkernel/dot-maker` library can be used to programmatically generate project files and directories.
It can be added to your API installation by following the [official documentation](https://docs.dotkernel.org/dot-maker/).

## Folder and files structure

The below files structure is what we will have at the end of this tutorial and is just an example,
you can have multiple components such as event listeners, wrappers, etc.

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
* `src/Book/src/RoutesDelegator.php` – a routes delegator is a delegator factory responsible for configuring routing middleware based on routing configuration provided by the application
* `src/Core/src/Book/src/Entity/Book.php` – an entity refers to a PHP class that represents a persistent object or data structure
* `src/Core/src/Book/src/Repository/BookRepository.php` – a repository is a class responsible for querying and retrieving entities from the database
* `src/Core/src/Book/src/ConfigProvider.php` – is a class that provides configuration for Doctrine ORM

## File creation and contents

After successfully installing `dot-maker`, it can be used to generate the Book module.
Invoke `dot-maker` by executing `./vendor/bin/dot-maker` or via the optional script described in the documentation - `composer make`.
This will list all component types that can be created - for the purposes of this tutorial, enter `module`:

```shell
./vendor/bin/dot-maker module
```

Type `book` when prompted to enter the module name.

Next you will be prompted to add the relevant components of a module, accepting `y(es)`, `n(o)` and `Enter` (defaults to `yes`):

> Note that `dot-maker` will automatically split the files into the described `Api` and `Core` structure without a further input needed.

* `Entity and repository` (Y): will generate the `Book.php` entity and the associated `BookRepository.php`.
* `Service` and `service interface` (Y): will generate the `BookService` and the `BookServiceInterface`.
* `Command`, followed by `middleware`(N): not necessary for the module described in this tutorial.
* `Handler` (Y): this option is needed, and will further prompt you for the required actions.
    * `Allow listing Books?` (Y): this will generate both the `GetBookResourceHandler.php` class and the `BookCollection.php` it uses.
    * `Allow viewing Books?` (Y): will generate the single resource GET action handler - `GetBookResourceHandler.php`.
    * `Allow creating Books?` (Y): will generate the POST action handler for the `Book` entity - `PostBookResourceHandler.php`, as well as the input filter used for validating the data - `CreateBookInputFilter.php`.
    * `Allow deleting Books?`, `Allow editing Books?` and `Allow replacing Books?` (N): will generate handlers that reflect the DELETE, PATCH and PUT actions respectively, but are not necessary for this tutorial.
* Following this step, `dot-maker` will automatically generate the `ConfigProvider.php` classes for both the `Api` and `Core` namespaces, as well as the `OpenAPI.php` class which automatically documents the previously generated routes.

You will then be instructed to:

* Register the `ConfigProvider` classes by adding `Api\Book\ConfigProvider::class` and `Core\Book\ConfigProvider::class` to `config/config.php`
* Register the new `Book` namespace by adding `"Api\\Book\\": "src/Book/src/"` and `"Core\\Book\\": "src/Core/src/Book/src/"` to `composer.json` under the `autoload.psr-4` key.
    * After registering the namespace, run the following command to regenerate the autoloaded files, as notified by `dot-maker`:

```shell
composer dump
```

* `dot-maker` will by default prompt you to generate the migrations for the new entity, but for the purpose of this tutorial
we will run this after updating the generated entity.

The next step is filling in the required logic for the proposed flow of this module.
While `dot-maker` does also include common logic in the relevant files, the tutorial adds custom functionality.
As such, the following section will go over the files that require changes.

* `src/Core/src/Book/src/Entity/Book.php`

To keep things simple in this tutorial, our book will have three properties: `name`, `author` and `releaseDate`.
Add the three properties and their getters and setters, while making sure to update the generated constructor method.

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

The `BookService` class will require minor modifications for the `getBooks()` and `saveBook()` methods, to add the custom properties added in the previous step.
The class should look like the following after updating the methods.

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

use function in_array;

class BookService implements BookServiceInterface
{
    #[Inject(
        BookRepository::class,
    )]
    public function __construct(
        protected BookRepository $bookRepository,
    ) {
    }

    public function getBookRepository(): BookRepository
    {
        return $this->bookRepository;
    }

    public function deleteBook(
        Book $book,
    ): void {
        $this->bookRepository->deleteResource($book);
    }

    /**
     * @param array<non-empty-string, mixed> $params
     */
    public function getBooks(
        array $params,
    ): QueryBuilder {
        $filters = $params['filters'] ?? [];
        $params  = Paginator::getParams($params, 'book.created');

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

    /**
     * @param array<non-empty-string, mixed> $data
     */
    public function saveBook(
        array $data,
        ?Book $book = null,
    ): Book {
        if (! $book instanceof Book) {
            $book = new Book(
                $data['name'],
                $data['author'],
                new DateTimeImmutable($data['releaseDate'])
            );
        }

        $this->bookRepository->saveResource($book);

        return $book;
    }
}

```

When creating or updating a book, we will need some validators, so we will create input filters that will be used to validate the data received in the request.

By creating a `module` with `dot-maker`, separate inputs will not be created.
However, you can still generate them as using these steps:

* Run the following to start adding `Input` classes:

```shell
./vendor/bin/dot-maker input
```

* When prompted, enter the names `Author`, `Name` and `ReleaseDate` one by one to generate the classes.
* The resulting `AuthorInput.php`, `NameInput.php` and `ReleaseDateInput.php` classes require no further changes for the tutorial use case.

The module creation process has generated the parent input filter `CreateBookInputFilter.php` with an empty constructor.
Now we add all the inputs together in the parent input filter's `__construct`, as below:

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

> Note that `dot-maker` will not generate inputs in the constructor, so the following are to be added by hand **if** going for this approach.

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

## Migrations

All changes are done, so at this point the migration file can be generated to create the associated table for the `Book` entity.

> You can check the mapping files by running:

```shell
php ./bin/doctrine orm:validate-schema
```

> Generate the migration files by running:

```shell
php ./vendor/bin/doctrine-migrations diff
```

This will check for differences between your entities and database structure and create migration files if necessary, in `src/Core/src/App/src/Migration`.

To execute the migrations run:

```shell
php ./vendor/bin/doctrine-migrations migrate
```

## Update the authorization file

We need to configure access to the newly created endpoints.
Open `config/autoload/authorization.global.php` and append the below route names to the `UserRoleEnum::Guest->value` key:

* `book::list-books`
* `book::view-book`
* `book::create-book`

> Make sure you read and understand the `rbac` [documentation](https://docs.dotkernel.org/dot-rbac-guard/v4/configuration/).

## Checking endpoints

First, we start a local server by executing:

```shell
composer serve
```

If we did everything as planned, we should be able to create a new book by executing the below command:

```shell
curl -X POST http://0.0.0.0:8080/book \
  -H "Content-Type: application/json" \
  -d '{"name": "test", "author": "author name", "releaseDate": "2025-08-21"}'
```

To list the books use:

```shell
curl http://0.0.0.0:8080/book
```

To fetch a book, `curl` one of the links found in the output of the **list books** command, under `_embedded` . `books` . * . `_links` . `self` . `href`.

The link should have the following format:

```shell
curl http://0.0.0.0:8080/book/{uuid}
```
