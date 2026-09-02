# Content Negotiation

## Summary

Content negotiation matches what a client says it is sending and what it wants back against what the API can actually handle.
Dotkernel API validates the `Accept` and `Content-Type` headers in middleware, per route or from a mandatory `default` entry in `config/autoload/content-negotiation.global.php`, returning `406 Not Acceptable` or `415 Unsupported Media Type` when a format is not supported.

## Details

> Introduced in Dotkernel API 5.0.0

An application performs **Content Negotiation** to:

- match the requested format as specified by the client via the `Accept` header with a format the application can deliver.
- determine the `Content-Type` of incoming data and deserialize it so the application can use it.

Essentially, content negotiation is the *client* telling the server what it is sending and what it wants in return, and the server determining if it can do what the client requests.

Content negotiation validation in **Dotkernel API** happens through middleware, and it ensures that the incoming request and the outgoing response conform to the content types specified in the config file for all routes or for a specific route.
It performs validation on the `Accept` and `Content-Type` headers of the request and response.
It returns appropriate error responses when necessary.

## Configuration

In Dotkernel API the configuration file for content negotiation is `config/autoload/content-negotiation.global.php`.
The contents look like this:

```php
return [
    'content-negotiation' => [
        'default'         => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [
                'application/json',
                'application/hal+json',
            ],
        ],
        'your.route.name' => [
            'Accept'       => [],
            'Content-Type' => [],
        ],
    ],
];
```

Excepting the `default` key, all your keys must match the route name.
For example, in Dotkernel API we have the route to list all admins, whose name is `admin.list`.
If you did not specify content negotiation for a given route, the `default` setup will be used.
The `default` key is mandatory.

Every route configuration must come with `Accept` and `Content-Type` keys.
These keys will be used as request headers for validation.

## Accept Negotiation

This specifies that your server can return that format, or at least one of the formats sent by the client.

```shell
GET /admin HTTP/1.1
Accept: application/json
```

This request indicates the client wants `application/json` in return.
The server will use the config file to see if that format can be returned, basically if `application/json` is present in the `Accept` key.

- If the format cannot be returned, a status code `406 - Not Acceptable` will be returned.
- If the format can be returned, the server should report the media type through the `Content-Type` header in the response.

> Due to how these validations are made, the server can return a more generic media type, e.g., for a `json` media type.
> For example, if the client sends `Accept: application/vnd.api+json`, but you configured your `Accept` key as `application/json`, the format will still be returned as `json`.

> If the `Accept` header of the request contains `*/*` it means that whatever format the server can return is OK.

## Content-Type Negotiation

The second aspect of content negotiation is the `Content-Type` header and to determine if the server can deserialize the data.

```shell
POST /admin/1 HTTP/1.1
Accept: application/json
Content-Type: application/json
{
    "foo": "bar"
}
```

The server will try to validate the `Content-Type` header against your configured `Content-Type` key from the config file, and if the format is not supported, a status code `415 - Unsupported Media Type` will be returned.

For example, if you have a route that needs a file to be uploaded, normally you will configure the `Content-Type` of that route to be `multipart/form-data`.
The above request will fail because the client sends `application/json` as `Content-Type`.

> If the request does not contain a "Content-Type" header, that means that the server will try to deserialize the data to the best of its abilities.

## The `Request <-> Response` validation

In addition to the validation described above, a third and last one occurs.
The server will check if the format in the `Accept` header for the request can be returned in the response.

The way **Dotkernel API** returns a response in handler means a content type is always set.
This cannot be the case in any custom response, but the server will always check the  `Content-Type` for the response and will try to validate that against the `Accept` header of the request.
If the validation fails, a status code `406 - Not Acceptable` will be returned.

## FAQ

**Q: What is the difference between a 406 and a 415 response?**

A: `406 Not Acceptable` means the API cannot produce the format the client asked for in `Accept`.
`415 Unsupported Media Type` means the API cannot consume the format the client sent in `Content-Type`.

**Q: Which configuration keys are required?**

A: `default` is mandatory, and every entry — including `default` — must define both `Accept` and `Content-Type`.

**Q: How do I configure negotiation for one specific route?**

A: Add a key matching the route name, for example `admin.list`, alongside `default`.
Routes without their own key fall back to `default`.

**Q: What happens when the client sends `Accept: */*`?**

A: Any format the API can produce is acceptable, so the check passes.

**Q: What if the request has no `Content-Type` header?**

A: The server attempts to deserialize the body as best it can, rather than rejecting the request outright.

**Q: Will `application/vnd.api+json` be rejected if I only configured `application/json`?**

A: No. Validation resolves to the more generic media type, so the request is served as JSON.

**Q: How do I configure a file upload endpoint?**

A: Set that route's `Content-Type` to `multipart/form-data`.
Clients sending `application/json` to it will then receive a 415.

**Q: What is the third validation pass for?**

A: It confirms that the response's `Content-Type` satisfies the request's `Accept` header.
Handler responses always set a content type, but a custom response may not, and a mismatch results in a 406.

**Q: Since which version is this available?**

A: Dotkernel API 5.0.0.
