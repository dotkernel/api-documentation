# Exceptions

## Summary

Dotkernel API expresses error conditions through ten problem-specific exceptions under `Api\App\Exception`, each carrying the HTTP status code it maps to.
All of them implement `Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface`, are built through a static `::create()` factory rather than with `new`, and are rendered by `ProblemDetailsMiddleware` as an RFC 9457 problem details document.
This page lists when to throw each one, shows the response it produces, and walks through adding a custom exception with its own status code.

## What are exceptions?

Exceptions are a powerful mechanism for handling errors and other exceptional conditions that may occur during the execution of a script.
They provide a way to manage errors in a structured and controlled manner, separating error-handling code from regular code.

## How we use exceptions

When it comes to handling exceptions, **Dotkernel API** relies on the usage of easy-to-understand, problem-specific exceptions.
They all live in `src/App/src/Exception/` and share a single shape, shown here for `BadRequestException`:

```php
<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class BadRequestException extends Exception implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param non-empty-string $detail
     * @param array<string, mixed> $additional
     */
    public static function create(string $detail, string $type = '', string $title = '', array $additional = []): self
    {
        $exception = new self();

        $exception->type       = $type;
        $exception->detail     = $detail;
        $exception->status     = StatusCodeInterface::STATUS_BAD_REQUEST;
        $exception->title      = $title;
        $exception->additional = $additional;

        return $exception;
    }
}
```

Two things follow from that shape, and both matter when you write code that throws:

- **The status code belongs to the class, not to the throw site.** `create()` sets `status` itself, so choosing `BadRequestException` *is* choosing `400`. There is no way to throw one of these with a different status, and nothing in the application catches an exception in order to change its status.
- **Always construct with `::create()`.** The properties the interface exposes are declared by `CommonProblemDetailsExceptionTrait` and are not constructor arguments, so `new BadRequestException('some message')` compiles but produces an exception with no status, no detail and no title.

`create()` takes the same four arguments in every one of the ten classes:

| Argument | Becomes | When omitted |
| --- | --- | --- |
| `$detail` | `detail` — what went wrong on this specific request | required |
| `$type` | `type` — a URI identifying the class of problem | derived from the status code |
| `$title` | `title` — a short summary of the problem type | the HTTP reason phrase for the status |
| `$additional` | extra top-level members, such as field-level validation errors | nothing is added |

A typical throw supplies the detail and, where there is more to say, additional members:

```php
throw BadRequestException::create(
    detail: Message::VALIDATOR_INVALID_DATA,
    additional: ['errors' => $this->inputFilter->getMessages()]
);
```

### Available exceptions

| Exception | Status | Raised by |
| --- | --- | --- |
| `BadRequestException` | `400 Bad Request` | handlers, on input filter failure |
| `RuntimeException` | `400 Bad Request` | `Renderer`, `HandlerService`, `ErrorReportService`, `HandlerDelegatorFactory` |
| `UnauthorizedException` | `401 Unauthorized` | `ErrorReportPermissionMiddleware`, `ErrorReportService` |
| `SunsetException` | `401 Unauthorized` | `BaseDeprecation`, on an invalid `sunset` date |
| `ForbiddenException` | `403 Forbidden` | `ErrorReportPermissionMiddleware`, `ErrorReportService` |
| `NotFoundException` | `404 Not Found` | handlers and services, for a missing resource |
| `NotAcceptableException` | `406 Not Acceptable` | `ContentNegotiationMiddleware` |
| `ConflictException` | `409 Conflict` | `UserService`, `AdminService`, `DeprecationMiddleware`, `ResourceProviderMiddleware` |
| `ExpiredException` | `410 Gone` | the password reset handlers |
| `UnsupportedMediaTypeException` | `415 Unsupported Media Type` | `ContentNegotiationMiddleware` |

Note that `RuntimeException` and `SunsetException` do not carry the status you might expect from their names — both are listed above with the status their `create()` method actually sets.

### `BadRequestException` thrown when

- The client tries to **create/update a resource**, but the **request data is invalid/incomplete** (example: client tries to create an account, but does not send the required `identity` field)

This is the exception input filter failures raise, and the one you will throw most often.

### `RuntimeException` thrown when

- A **template cannot be found** by `Api\App\Template\Renderer`
- A **service or route middleware is missing or misconfigured**, such as remote error reporting being called while disabled

It extends PHP's own `\RuntimeException` rather than `\Exception`, and returns `400 Bad Request`.

### `UnauthorizedException` thrown when

- The **resource cannot be accessed** because the **client is not authenticated**

### `SunsetException` thrown when

- A `ResourceDeprecation` attribute is given a **`sunset` value that is not a valid date**

This is a programming error surfaced at attribute construction, not a condition a client can trigger.
See [API Evolution pattern](../tutorials/api-evolution.md).

### `ForbiddenException` thrown when

- The **resource cannot be accessed** by the authenticated client's **role**

Note that this is not what a failed RBAC check produces — see [How it works](#how-it-works) below.

### `NotFoundException` thrown when

- The client tries to interact with a **resource that does not exist** on the server (example: client sends a `GET /resource-does-not-exist` request)

### `NotAcceptableException` thrown when

- The request's **`Accept` header asks for a format the route does not support**
- The **response cannot be produced** in any of the formats the client accepts

### `ConflictException` thrown when

- The **resource cannot be created** because a different resource with the same identifier **already exists** (example: cannot change existing user's identity because another user with the same identity already exists)
- The **resource cannot change its state** because it is **already in the specified state** (example: user cannot be activated because it is already active)

### `ExpiredException` thrown when

- The **resource cannot be accessed**
    - because it has **expired** (example: account activation link)
    - because it has been **consumed** (example: one-time password)

### `UnsupportedMediaTypeException` thrown when

- The request's **`Content-Type` is not one the route accepts**

## How it works

During a request, if there is no uncaught exception, **Dotkernel API** will return a response with the data provided by the handler that processed the request.

Otherwise, the response is built by `Mezzio\ProblemDetails\ProblemDetailsMiddleware`, which `config/pipeline.php` pipes as the outermost layer precisely so that it sees every throwable the application raises.
It takes one of two paths.

**The exception implements `ProblemDetailsExceptionInterface`.**
Its status, detail, title, type and additional members are used as they are.
All ten exceptions above take this path, so a `BadRequestException` thrown from an account creation handler produces:

```json
{
    "errors": {
        "identity": {
            "isEmpty": "Value is required and can't be empty"
        }
    },
    "title": "Bad Request",
    "type": "https://datatracker.ietf.org/doc/html/rfc9110#name-400-bad-request",
    "status": 400,
    "detail": "The submitted request contains invalid data."
}
```

The `type` URI comes from the `default_types_map` in `config/autoload/problem-details.global.php`, which maps the ten statuses the application uses to the matching section of RFC 9110.
A status absent from that map falls back to `https://httpstatus.es/{status}`.

**Anything else.**
`Dot\Mail\Exception\MailException`, PHP errors and any exception of your own that does not implement the interface take this path.
The response is a `500 Internal Server Error` whose `detail` reads `An unknown error occurred.` — the real message is withheld unless debug mode or `exceptionDetailsInResponse` is enabled, so in production nobody learns anything from it beyond the status.

Two error responses do not come from an application exception at all:

- **`405 Method Not Allowed`** is produced by `Mezzio\Router\Middleware\MethodNotAllowedMiddleware` when a route exists but not for that method. There is no `MethodNotAllowedException` in the codebase.
- **`404 Not Found`** for an unmatched URL is produced by `ProblemDetailsNotFoundHandler` at the end of the pipeline, before a route is ever dispatched.

One error response is neither of the two, and it is worth knowing about because it is the one clients hit most:

- **A failed RBAC check** returns `403 Forbidden` from `AuthorizationMiddleware`, which builds the response itself rather than throwing `ForbiddenException`. Its body is the older `{"error": {"messages": [...]}}` envelope, not a problem details document. Clients that parse error responses need to handle both shapes. See [Authorization](authorization.md).

## How to extend

In this example we will

- Create a custom exception called `TeapotException`
- Place it next to the already existing custom exceptions (you can use your preferred location)
- Return a custom HTTP status code when `TeapotException` is encountered

Because the status travels with the exception, that is the whole job — there is no pipeline or handler change to make.

### Step 1: Create the exception

Navigate to the directory `src/App/src/Exception` and create a PHP class called `TeapotException.php`.
Open `TeapotException.php` and add the following content:

```php
<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class TeapotException extends Exception implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param non-empty-string $detail
     * @param array<string, mixed> $additional
     */
    public static function create(string $detail, string $type = '', string $title = '', array $additional = []): self
    {
        $exception = new self();

        $exception->type       = $type;
        $exception->detail     = $detail;
        $exception->status     = StatusCodeInterface::STATUS_IM_A_TEAPOT;
        $exception->title      = $title;
        $exception->additional = $additional;

        return $exception;
    }
}
```

Save and close the file.

The two `use` statements from `Mezzio\ProblemDetails` are what make this exception renderable: the interface is how `ProblemDetailsMiddleware` recognises it, and the trait supplies the properties `create()` fills in.

### Step 2: Throw the exception

Open the file `src/App/src/Handler/GetIndexResourceHandler.php` and replace the body of the `handle` method with the following:

```php
throw TeapotException::create('I refuse to brew coffee.');
```

Add the import at the top of the file:

```php
use Api\App\Exception\TeapotException;
```

Save and close the file.

### Step 3: Test

Access your API's home page URL.
It returns a `418 I'm a teapot` HTTP status code and the following content:

```json
{
    "title": "I'm a teapot",
    "type": "https://httpstatus.es/418",
    "status": 418,
    "detail": "I refuse to brew coffee."
}
```

The `title` was filled in from the status code's reason phrase, and the `type` fell back to `httpstatus.es` because `418` is not in `default_types_map`.
To control both, pass them to `create()` at the throw site, or give `create()` its own defaults in the class.
To give every `418` response the same type, add the status to `default_types_map` in `config/autoload/problem-details.global.php`.

Revert the change to `GetIndexResourceHandler` when you are done.

## FAQ

**Q: Which exception should I throw for invalid request data?**

A: `BadRequestException`, which produces a `400 Bad Request`.
It is what input filter failures raise.
See [Injectable input filters](../extended-features/injectable-input-filters.md).

**Q: What is the difference between `UnauthorizedException` and `ForbiddenException`?**

A: `UnauthorizedException` (401) means the client is not authenticated at all; `ForbiddenException` (403) means it is authenticated but its role does not grant access.
Note that a failed RBAC check does not throw `ForbiddenException` — `AuthorizationMiddleware` builds that `403` response itself.
See [Authorization](authorization.md).

**Q: When do I use `ConflictException`?**

A: When a resource cannot be created because one with the same identifier exists, or cannot change state because it is already in that state.
It returns `409 Conflict`.

**Q: What does `ExpiredException` cover?**

A: Resources that can no longer be used because they expired, such as an activation link, or because they were already consumed, such as a one-time password.
It returns `410 Gone`.

**Q: Why can I not pass a message to the constructor?**

A: The `detail`, `title`, `type`, `status` and `additional` properties come from `CommonProblemDetailsExceptionTrait` and are set by `create()`, not by `__construct()`.
`new NotFoundException(Message::USER_NOT_FOUND)` leaves the exception with no status, so use `NotFoundException::create(Message::USER_NOT_FOUND)` instead.

**Q: How do I map a custom exception to a specific status code?**

A: Set the status inside the exception's own `create()` method, as [How to extend](#how-to-extend) shows.
No `catch` block is involved, and there is nowhere in the application that maps exception classes to status codes.

**Q: Why does my custom exception return `500 Internal Server Error`?**

A: Because it does not implement `ProblemDetailsExceptionInterface`, so `ProblemDetailsMiddleware` treats it as an unexpected failure.
Implement the interface and use the trait, as in Step 1 above.

**Q: What happens to an exception I do not handle?**

A: Any throwable that does not implement `ProblemDetailsExceptionInterface` — `MailException` included — produces a `500 Internal Server Error` with the detail `An unknown error occurred.`, and the original message is only shown when debug mode is enabled.

**Q: How do exceptions relate to problem details responses?**

A: The exceptions carry the title, type, status, detail and any additional fields that the problem details middleware renders.
See [Problem details](../extended-features/problem-details.md).
