# Content Negotiation

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
