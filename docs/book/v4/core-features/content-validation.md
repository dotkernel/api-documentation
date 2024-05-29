# Content Negotiation

**Content Negotiation** is performed by an application in order :

- To match the requested representation as specified by the client via the Accept header with a representation the
  application can deliver.
- To determine the `Content-Type` of incoming data and deserialize it so the application can utilize it.

Essentially, content negotiation is the *client* telling the server what it is sending and what it wants in return, and
the server determining if it can do what the client requests.

Content negotiation validation in **DotKernel API** happened through middleware, and it ensures that the incoming
request and the outgoing response conform to the content types specified in the config file for all routes or for a
specific route.

It performs validation on the `Accept` and `Content-Type` headers of the request and response and returning appropriate
errors responses when necessary.

## Configuration

In DotKernel the configuration file for content negotiation is held on `config/autoload/content-negotiation.global.php`
and the array look like this:

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

Except the `default` key, all your key must match the route name, for example in DotKernel we have the route to list all
admins, which name is `admin.list`.

If you did not specify a route name to configure you specifications about content negotiation, the `default` one will
be in place. The `default` key is `mandatory`.

Every route configuration must come with `Accept` and `Content-Type` keys, basically this will be the keys that the
request headers will be validated against.

## Accept Negotiation

This specifies that your server can return that representation, or at least one the representation send from the client

```http request
GET /admin HTTP/1.1
Accept: application/json
```

This request indicates the client wants `application/json` in return. Now the server, through the config file will try
to validate if that representation can be returned, basically if `application/json` is presented in the `Accept` key.

If the representation cannot be returned, a status code `406 - Not Acceptable` will be returned.

If the representation can be returned, the server should report the media type through `Content-Type` header of the
response.

> Due to how these validations are make, for a `json` media type, the server can return a more generic media type,
> for example, if the clients send `Accept: application/vnd.api+json` and you configured your `Accept` key
> as `application/json`
> the representation will be returned as is still json.

> If the `Accept` header of the request contains `*/*` it means that whatever the server can return is OK, so it can
> return anything

## Content-Type Negotiation

This aspect of content negotiation is the `Content-Type` key and determining if the server can deserialize the data.

``` http request
POST /admin/1 HTTP/1.1
Accept: application/json
Content-Type: application/json
{
    "foo": "bar"
}
```

The server will try to validate this `Content-Type` against your configured `Content-Type` key from the config file,
and if the format is not supported, a status code `415 - Unsupported Media Type` will be returned.

For example, you have a route that it needs an upload file, normally you will configure the `Content-Type` of that route
to be `multipart/form-data`. The above request will fail as the client send `application/json` as `Content-Type`

> If the request does not contain "Content-Type" header, that means that the server will try to deserialize tha data as
> he can.

## The `Request <-> Response` validation

In addition to the validation described above, a third one is happening and is the last one, the server will check if
the request `Accept` header can really be returned by the response.

Through how the **DotKernel API** is returning a response in handler , a content type is always set, but this cannot be
the case in any custom response but in any way the server will check what `Content-Type` the response is returning and 
will try to validate that against the `Accept` header of the request. If the validation fails, a status code
`406 - Not Acceptable` will be returned.
