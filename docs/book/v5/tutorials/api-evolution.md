# API Evolution pattern

API evolution: Updating an API while keeping it compatible for existing consumers by adding new features, fixing bugs, 
planning and removing outdated features.

## How it works

In DotKernel API we can mark an entire endpoint or a single method as deprecated using attributes on handlers.
We use response headers to inform the consumers about the future changes by using 2 new headers:

1) `Link` - it's a link to the official documentation pointing out the changes that will take place.
2) `Sunset` - this header is a date, indicating when the deprecated resource will potentially become unresponsive.

**Both headers are independent, you can use them separately.**

> Make sure you have the `DeprecationMiddleware:class` piped in your `pipeline` list. In our case it's
> `config/pipeline.php`.

### Marking an entire endpoint as deprecated

When you want to mark an entire resource as deprecated you have to use the `ResourceDeprecation` attribute.

```php
...
#[ResourceDeprecation(
    sunset: '2038-01-01',
    link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
    deprecationReason: 'Resource deprecation example.',
    rel: 'sunset',
    type: 'text/html'
)]
class HomeHandler implements RequestHandlerInterface
{
...
```

In the example above, the ``ResourceDeprecation`` attribute is attached to the class, marking the entire `/` (home) 
endpoint as deprecated starting from `2038-01-01`.

Running the following curl will print out the response headers where we can see the **Sunset** and **Link** headers.

```shell
curl --head -X GET http://0.0.0.0:8080 -H "Content-Type: application/json"
```

```shell
HTTP/1.1 200 OK
Host: 0.0.0.0:8080
Date: Mon, 24 Jun 2024 10:23:11 GMT
Connection: close
X-Powered-By: PHP/8.2.20
Content-Type: application/json
Permissions-Policy: interest-cohort=()
Sunset: 2038-01-01
Link: https://docs.dotkernel.org/api-documentation/v5/core-features/versioning;rel="sunset";type="text/html"
Vary: Origin
```

### Marking a method as deprecated

Most of the time you want to deprecate only an endpoint, so you will need to use the `MethodDeprecation` attribute which
has the same parameters, but it attaches to a handler method.

```php
...
class HomeHandler implements RequestHandlerInterface
{
    ...
    use Api\App\Attribute\MethodDeprecation;

    #[MethodDeprecation(
        sunset: '2038-01-01',
        link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
        deprecationReason: 'Method deprecation example.',
        rel: 'sunset',
        type: 'text/html'
    )]
    public function get(): ResponseInterface
    {
        ...
    }
}
```

Attaching the `MethodDeprecation` can only be done to HTTP verb methods (`GET`, `POST`, `PUT`, `PATCH` and `DELETE`).

If you followed along you can run the below curl:

```shell
curl --head -X GET http://0.0.0.0:8080 -H "Content-Type: application/json"
```

```shell
HTTP/1.1 200 OK
Host: 0.0.0.0:8080
Date: Mon, 24 Jun 2024 10:54:57 GMT
Connection: close
X-Powered-By: PHP/8.2.20
Content-Type: application/json
Permissions-Policy: interest-cohort=()
Sunset: 2038-01-01
Link: https://docs.dotkernel.org/api-documentation/v5/core-features/versioning;rel="sunset";type="text/html"
Vary: Origin
```

### NOTES

> If `Link` or `Sunset` do not have a value they will not appear in the response headers.

> `Sunset` has to be a **valid** date, otherwise it will throw an error.

> You **cannot** use both `ResourceDeprecation` and `MethodDeprecation` in the same handler.

> Deprecations can only be attached to handler classes that implement `RequestHandlerInterface`.

> The `rel` and `type` arguments are optional, they default to `sunset` and `text/html` if no value was provided and
> are `Link` related parts.
