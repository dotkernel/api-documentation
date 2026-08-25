# Writing documentation

> In order to avoid polluting PHP files with maybe thousands of lines of OpenAPI attributes, we opted for storing them
> in separate files, called `OpenAPI.php`, one for each module.

We already covered all the endpoints available in Dotkernel API, you can consult the existing documentation in each
module's own `OpenAPI.php` file. After you add more functionalities to your API, you will have to document the new
endpoints. This is easier than it sounds because in most cases you will do the same: add a request by method, describe
the request payload (if any), add request parameters (if any) and describe the possible responses.

## Common objects

To do this, you will use the following request objects:

- `OA\Delete`: delete an API resource identified by its unique id
- `OA\Get`: fetch API single or collections of API resources
- `OA\Post`: create a new API resource (unless if it already exists)
- `OA\Patch`: update an existing API resource
- `OA\Put`: create a new API resource (if it already exists, it is overwritten)

Also, the following components describing PHP objects:

- `OA\Schema`: describe an object sent in a request or received as a response -
[read more](https://spec.openapis.org/oas/latest.html#schema-object)
- `OA\Parameter`: describe a `query`/`path` parameter -
[read more](https://spec.openapis.org/oas/latest.html#parameter-object)
- `OA\RequestBody`: describe the body of a request -
[read more](https://spec.openapis.org/oas/latest.html#request-body-object)

There are lot more, but these are the most often used ones.

If you need help, take a look at the existing definitions found in Dotkernel API.

### OA\Delete

Defines a `DELETE` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{uuid}` - where `uuid` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used - omit if the endpoint is not protected
- `tags`: an array of tags to help grouping related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their
respective response bodies

### OA\Get

Defines a `GET` HTTP request. It should specify at least the following parameters:

- `path`: the route to a single or collection of resources (example: `/resource/{uuid}` for a single resource or
`/resource` for a collection of resources)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used - omit if the endpoint is not protected
- `tags`: an array of tags to help grouping related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their
respective response bodies

### OA\Patch

Defines a `PATCH` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{uuid}` - where `uuid` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used - omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help grouping related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their
respective response bodies

### OA\Post

Defines a `POST` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{uuid}` - where `uuid` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used - omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help grouping related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their
respective response bodies

### OA\Put

Defines a `PUT` HTTP request. It should specify at least the following parameters:

- `path`: the route to the resource (example: `/resource/{uuid}` - where `uuid` is a path parameter defined below)
- `description`: verbose description of the endpoint's purpose
- `summary`: short description of the endpoint's purpose
- `security`: an array of security scheme(s) to be used - omit if the endpoint is not protected
- `requestBody`: a `OA\RequestBody` object describing the data being sent in the request
- `tags`: an array of tags to help grouping related requests (example: user-related requests could have a `User` tag)
- `parameters`: an array of `query`/`path` parameters - each parameter is specified as a new `OA\Parameter` object
- `responses`: an array of `OA\Response` objects, each describing a combination of HTTP status codes and their
respective response bodies

## Conclusion

To summarize, the typical scenario on working on your own instance of Dotkernel API would follow these steps:

- create new module (example: `Book`)
- add functionality to your new module (routes, entities, repositories, handlers, services, tests etc)
- create file `OpenAPI.php` in the new module and describe each new endpoint
- generate latest version of documentation file as described [in this tutorial](./generate-documentation.md)
