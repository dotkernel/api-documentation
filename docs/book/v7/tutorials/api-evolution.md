# API Evolution pattern

## Summary

Dotkernel API lets you evolve endpoints without breaking existing consumers by marking a handler with the `ResourceDeprecation` attribute.
The `DeprecationMiddleware` then adds `Sunset` and `Link` response headers, telling consumers when the resource may stop responding and where the change is documented.

## Details

API evolution: Updating an API while keeping it compatible for existing consumers by adding new features, fixing bugs, planning and removing outdated features.

## How it works

In Dotkernel API we can mark an endpoint as deprecated using attributes on handlers.
We use response headers to inform the consumers about the future changes by using two new headers:

- `Link` - it's a link to the official documentation pointing out the changes that will take place.
- `Sunset` - this header is a date, indicating when the deprecated resource will potentially become unresponsive.

**The above headers are independent, so you can use them separately.**

> Make sure you have the `DeprecationMiddleware:class` added to your `pipeline` list.
> In our case it's `config/pipeline.php`.

## Marking an endpoint as deprecated

When you want to mark a resource as deprecated, you have to use the `ResourceDeprecation` attribute.

```php
...
#[ResourceDeprecation(
    sunset: '2038-01-01',
    link: 'https://docs.dotkernel.org/api-documentation/v7/tutorials/api-evolution/',
    deprecationReason: 'Resource deprecation example.',
    rel: 'sunset',
    type: 'text/html'
)]
class HomeHandler implements RequestHandlerInterface
{
}
```

In the example above, the `ResourceDeprecation` attribute is attached to the class, marking the `/` (home) endpoint as deprecated starting from `2038-01-01`.

Running the following curl will print out the response headers where we can see the **Sunset** and **Link** headers.

```shell
curl --head -X GET http://0.0.0.0:8080 -H "Content-Type: application/json"
```

```shell
HTTP/1.1 200 OK
Host: 0.0.0.0:8080
Date: Mon, 24 Jun 2024 10:23:11 GMT
Connection: close
X-Powered-By: PHP/6.4.20
Content-Type: application/json
Permissions-Policy: interest-cohort=()
Sunset: 2038-01-01
Link: https://docs.dotkernel.org/api-documentation/v7/tutorials/api-evolution/;rel="sunset";type="text/html"
Vary: Origin
```

## Notes

> If `Link` or `Sunset` do not have a value they will not appear in the response headers.

> `Sunset` has to be a **valid** date, otherwise it will throw an error.

> Deprecations can only be attached to handler classes that implement `RequestHandlerInterface`.

> The `rel` and `type` arguments are optional, they default to `sunset` and `text/html` if no value is provided and are `Link` related parts.

## FAQ

**Q: What do the `Sunset` and `Link` headers mean?**

A: `Sunset` is the date on which the deprecated resource may stop responding; `Link` points to documentation describing the change.
They are independent, so either can be used alone.

**Q: What do I need in place before deprecations work?**

A: `DeprecationMiddleware::class` must be present in your pipeline — in the default project, `config/pipeline.php`.
See [Middleware flow](../flow/middleware-flow.md).

**Q: Can I deprecate a single method rather than a whole resource?**

A: The `ResourceDeprecation` attribute is applied to the handler class, so the unit of deprecation is the handler.
Since each handler serves one method and route, deprecating the handler deprecates that method.

**Q: What happens if I leave `Sunset` or `Link` empty?**

A: The corresponding header is simply omitted from the response.

**Q: What if the `Sunset` date is invalid?**

A: It throws an error.
The value has to be a valid date.

**Q: Are `rel` and `type` required?**

A: No.
They default to `sunset` and `text/html`, and both relate to the `Link` header.

**Q: Which classes can carry a deprecation?**

A: Only handler classes implementing `RequestHandlerInterface`.

**Q: How do I check that the headers are being sent?**

A: Request the endpoint with `curl --head` and inspect the response headers.
