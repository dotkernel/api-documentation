# Writing documentation

## Summary

OpenAPI attributes live in a dedicated `OpenAPI.php` file per module rather than in the handlers themselves.
This page lists the request attributes (`OA\Get`, `OA\Post`, `OA\Patch`, `OA\Put`, `OA\Delete`), the component objects that describe payloads (`OA\Schema`, `OA\Parameter`, `OA\RequestBody`), and the parameters each request attribute should define.

## Details

> To avoid polluting PHP files with maybe thousands of lines of OpenAPI attributes, we opted for storing them in separate files, called `OpenAPI.php`, one for each module.

We already covered all the endpoints available in Dotkernel API, you can consult the existing documentation in each module's own `OpenAPI.php` file.
After you add more functionalities to your API, you will have to document the new endpoints.
This is easier than it sounds because in most cases you will do the same: add a request by method, describe the request payload (if any), add request parameters (if any) and describe the possible responses.

## Common objects

To do this, you will use the following request objects:

- `OA\Delete`: delete an API resource identified by its unique id
- `OA\Get`: fetch API single or collections of API resources
- `OA\Post`: create a new API resource (unless if it already exists)
- `OA\Patch`: update an existing API resource
- `OA\Put`: create a new API resource (if it already exists, it is overwritten)

Also, the following components describe PHP objects:

- `OA\Schema`: describe an object sent in a request or received as a response - [read more](https://spec.openapis.org/oas/latest.html#schema-object)
- `OA\Parameter`: describe a `query`/`path` parameter - [read more](https://spec.openapis.org/oas/latest.html#parameter-object)
- `OA\RequestBody`: describe the body of a request - [read more](https://spec.openapis.org/oas/latest.html#request-body-object)

There are a lot more, but these are the most often used ones.

If you need help, take a look at the existing definitions found in Dotkernel API.

### OA\Delete

Defines a `DELETE` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{id}` - where `id` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used—omit if the endpoint is not protected
- `tags`: an array of tags to help group related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their respective response bodies

### OA\Get

Defines a `GET` HTTP request. It should specify at least the following parameters:

- `path`: the route to a single or collection of resources (example: `/resource/{id}` for a single resource or `/resource` for a collection of resources)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used—omit if the endpoint is not protected
- `tags`: an array of tags to help group related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their respective response bodies

### OA\Patch

Defines a `PATCH` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{id}` - where `id` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used—omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help group related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their respective response bodies

### OA\Post

Defines a `POST` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{id}` - where `id` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used—omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help group related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their respective response bodies

### OA\Put

Defines a `PUT` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{id}` - where `id` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used—omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help group related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their respective response bodies

## Conclusion

To summarize, the typical scenario on working on your own instance of Dotkernel API would follow these steps:

- create new module (example: `Book`)
- add functionality to your new module (routes, entities, repositories, handlers, services, tests etc)
- create file `OpenAPI.php` in the new module and describe each new endpoint
- generate the latest version of a documentation file as described [in this tutorial](./generate-documentation.md)

## FAQ

**Q: Why are the attributes not placed on the handlers?**

A: A fully documented endpoint can run to hundreds of lines of attributes.
Keeping them in a per-module `OpenAPI.php` leaves the handlers readable.

**Q: Where do I start when documenting a new module?**

A: Copy the shape of an existing module's `OpenAPI.php`.
All the endpoints shipped with Dotkernel API are documented there and serve as working examples.

**Q: Which parameters should every request attribute define?**

A: `path`, `description`, `summary`, `tags`, `parameters` and `responses`, plus `requestBody` for the methods that accept a body, and `security` when the endpoint is protected.

**Q: How do I document an unprotected endpoint?**

A: Omit the `security` parameter.

**Q: What is the difference between `description` and `summary`?**

A: `summary` is a short one-line label; `description` is the verbose explanation.
Renderers display them in different places.

**Q: What are `tags` used for?**

A: Grouping related endpoints in the rendered documentation — for example tagging every user endpoint with `User`.

**Q: When do I use `OA\Post` versus `OA\Put`?**

A: `OA\Post` creates a new resource, while `OA\Put` creates it and overwrites an existing one.

**Q: Where do I find attributes not covered here?**

A: In the [OpenAPI specification](https://spec.openapis.org/oas/latest.html) and `zircote/swagger-php`'s examples.
See [Getting help](getting-help.md).

**Q: What is the order of work when adding a documented feature?**

A: Create the module, build its functionality, describe the endpoints in its `OpenAPI.php`, then regenerate the documentation file.
See [Generate documentation](generate-documentation.md).
