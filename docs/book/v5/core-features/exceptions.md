# Exceptions

## What are exceptions?

Exceptions are a powerful mechanism for handling errors and other exceptional conditions that may occur during the execution of a script.
They provide a way to manage errors in a structured and controlled manner, separating error-handling code from regular code.

## How we use exceptions

When it comes to handling exceptions, **Dotkernel API** relies on the usage of easy-to-understand, problem-specific exceptions.
Below we will list the available custom exceptions.

### `BadRequestException` thrown when

* The Client tries to **create/update resource**, but the **request data is invalid/incomplete** (example: client tries to create an account, but does not send the required `identity` field)

### `ConflictException` thrown when

* The **resource cannot be created** because a different resource with the same identifier **already exists** (example: cannot change existing user's identity because another user with the same identity already exists)
* The **resource cannot change its state** because it is **already in the specified state** (example: user cannot be activated because it is already active)

### `ExpiredException` thrown when

* The **resource cannot be accessed**
  * because it has **expired** (example: account activation link)
  * because it has been **consumed** (example: one-time password)

### `ForbiddenException` thrown when

* The **resource cannot be accessed** by the authenticated client's **role** (example: client authenticated as regular user sends a `GET /admin` request)

### `MethodNotAllowedException` thrown when

* The client tries to interact with a resource via an **invalid HTTP request method** (example: client sends a `PATCH /avatar` request)

### `NotFoundException` thrown when

* The client tries to interact with a **resource that does not exist** on the server (example: client sends a `GET /resource-does-not-exist` request)

### `UnauthorizedException` thrown when

* The **resource cannot be accessed** because the **client is not authenticated** (example: unauthenticated client sends a `GET /admin` request)

## How it works

During a request, if there is no uncaught exception, **Dotkernel API** will return a JSON response with the data provided by the handler that processed the request.

Otherwise, it will build and send a response based on the exception thrown:

* `BadRequestException` will return a `400 Bad Request` response
* `UnauthorizedException` will return a `401 Unauthorized` response
* `ForbiddenException` will return a `403 Forbidden` response
* `OutOfBoundsException` and `NotFoundException` will return a `404 Not Found` response
* `MethodNotAllowedException` will return a `405 Method Not Allowed` response
* `ConflictException` will return a `409 Conflict` response
* `ExpiredException` will return a `410 Gone` response
* `MailException`, `RuntimeException` and the generic `Exception` will return a `500 Internal Server Error` response

## How to extend

In this example we will
* Create a custom exception called `CustomException`
* Place it next to the already existing custom exceptions (you can use your preferred location)
* Return a custom HTTP status code when `CustomException` is encountered.

### Step 1: Create exception file

Navigate to the directory `src/App/src/Handler/Exception` and create a PHP class called `CustomException.php`.
Open `CustomException.php` and add the following content:

```php
<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Exception;

class CustomException extends Exception
{
}
```

Save and close the file.

### Step 2: Use exception file

Open the file `src/App/src/Handler/HomeHandler.php` and at the beginning of the `get` method, place the following code:

```php
throw new \Api\App\Exception\CustomException('some message');
```

Save and close the file.

### Step 3: Test for failure

Access your API's home page URL and make sure it returns `500 Internal Server Error` HTTP status code and the following content:

```json
{
    "error": {
        "messages": [
            "some message"
        ]
    }
}
```

### Step 4: Prepare for success

Open the file `src/App/src/Handler/HandlerTrait.php` and locate the `handle` method.
Insert the following lines of code before the first catch statement:

```php
        } catch (\Api\App\Exception\CustomException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_IM_A_TEAPOT);
```

Save and close the file.

### Step 5: Test for success

Access your API's home page URL, which should return the same content.
Notice that this time it returns `418 I'm a teapot` HTTP status code.
