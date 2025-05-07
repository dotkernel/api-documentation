# Problem details

With the usage of `mezzio/mezzio-problem-details` we have implemented a way to help the developers understand better the errors that they are getting from their APIs based on the [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html) standards.

Example of a response with details:

```json
{
    "title": "Unauthorized",
    "type": "https://docs.dotkernel.org/api-documentation/v5/core-features/error-reporting/",
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
Our exceptions have also been modified in order to be slimmed around the requirement for the `problem-details` package.

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
