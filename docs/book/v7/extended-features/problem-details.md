# Problem details

## Summary

Dotkernel API returns errors as RFC 9457 Problem Details documents via `mezzio/mezzio-problem-details`, so a failed request carries a title, type, status and detail instead of an opaque message.
This page shows the response shape, the middleware that produces it, the slimmed-down exceptions behind it, and where to map status codes to documentation links.

## Details

With the usage of `mezzio/mezzio-problem-details` we have implemented a way to help the developers understand better the errors that they are getting from their APIs based on the [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html) standards.

Example of a response with details:

```json
{
    "title": "Unauthorized",
    "type": "https://docs.dotkernel.org/api-documentation/v7/core-features/error-reporting/",
    "status": 401,
    "detail": "You are not allowed to report errors."
}
```

Usually the response includes:

- A title related to the error
- The type of error
- The status of the request (e.g `404`)
- Different error messages

More fields can be added based on the preference of the developer.

## Our changes

In order for us to implement this new feature, a new middleware component was required.
We have created `ProblemDetailsMiddleware` along with `ProblemDetailsNotFoundHandler` which is being called in the `config/pipeline.php` file.
Our exceptions have also been modified to be slimmed around the requirement for the `problem-details` package.

Example from `src/App/src/Exception/BadRequestException.php`:

```php
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
```

An example configuration file for setting custom links has also been created in `config/autoload/problem-details.global.php`.
Here the statuses of the API calls are being attributed to a link.

```php
return [
    'problem-details' => [
        'default_types_map' => [
            StatusCodeInterface::STATUS_BAD_REQUEST
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-400-bad-request',
            StatusCodeInterface::STATUS_UNAUTHORIZED
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-401-unauthorized',
            StatusCodeInterface::STATUS_FORBIDDEN
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-403-forbidden',
            StatusCodeInterface::STATUS_NOT_FOUND
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-404-not-found',
            StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-405-method-not-allowed',
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-406-not-acceptable',
            StatusCodeInterface::STATUS_CONFLICT
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-409-conflict',
            StatusCodeInterface::STATUS_GONE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-410-gone',
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-415-unsupported-media-type',
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-500-internal-server-error',
        ],
    ],
];
```

## FAQ

**Q: Which standard do the error responses follow?**

A: [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html), the Problem Details format for HTTP APIs.

**Q: What fields does a problem details response contain?**

A: At minimum a title, a type, the HTTP status and a detail message.
You can add further fields when a specific error needs more context.

**Q: Where are the middlewares registered?**

A: `ProblemDetailsMiddleware` and `ProblemDetailsNotFoundHandler` are wired into `config/pipeline.php`.
See [Middleware flow](../flow/middleware-flow.md).

**Q: How do I change the `type` link for a status code?**

A: Edit `default_types_map` in `config/autoload/problem-details.global.php` and point the status code at your own URL.

**Q: How do I raise a problem details error from my own code?**

A: Throw one of the project exceptions, for example `BadRequestException::create($detail)`.
The middleware converts it into the response.
See [Exceptions](../core-features/exceptions.md).

**Q: Can I attach extra data to an error response?**

A: Yes.
Pass the `additional` array to the exception's `create()` method and those fields appear alongside the standard ones.
