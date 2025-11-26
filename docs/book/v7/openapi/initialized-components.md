# Initialized OpenAPI components

Below you will find details on some prepopulated OpenAPI components we added to Dotkernel API.

## OA\Info

Defined in `src/App/src/OpenAPI.php`, this object provides general info about the API:

- `version`: API version (example: `1.0.0`)
- `title`: title shown in the UI (example: `Dotkernel API`)

For more info, see [this page](https://spec.openapis.org/oas/latest.html#info-object).

## OA\Server

Defined in `src/App/src/OpenAPI.php`, this object provides API server entries:

- `url`: API server URL (example: `https://api.example.com` - use no trailing slash!)
- `description`: describes the purpose of the server (example: `Dev`, `Staging`, `Production` or even `Auth` if you use a separate authentication server)

You can have multiple `Server` definitions, one for each of your Dotkernel API instances.

For more info, see [this page](https://spec.openapis.org/oas/latest.html#server-object).

## OA\SecurityScheme

Defined in `src/App/src/OpenAPI.php`, you will find an object for the `AuthToken` security header:

- `securityScheme`: the name of the security scheme—you will provide this to indicate that an endpoint is protected
- `type`: whether it's an API key, an authorization header etc.
- `in`: indicates where the scheme is applied (`query`/`header`/`cookie`)
- `bearerFormat`: a hint to the client to identify how the bearer token is formatted
- `scheme`: the name of the authorization scheme to be used

And another object for the `ErrorReportingToken` security token:

- `securityScheme`: the name of the security scheme—you will provide this to indicate that an endpoint is protected
- `type`: whether it's an API key, an authorization header etc.
- `in`: indicates where the scheme is applied (`query`/`header`/`cookie`)
- `name`: the name of the header

For more info, see [this page](https://spec.openapis.org/oas/latest.html#security-scheme-object).

## OA\ExternalDocumentation

Defined in `src/App/src/OpenAPI.php`, in this object we provide the following details:

- `description`: describes the purpose of the document
- `url`: external documentation URL

For more info, see [this page](https://spec.openapis.org/oas/latest.html#external-documentation-object).

## OA\Schema

Schemas are OpenAPI objects describing an object or collection of objects existing in your project.

### Schemas describing objects

To describe an object (entity), you will need to transform it into a schema.

Object:

```php
<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\RoleInterface;
use Api\App\Entity\TimestampsTrait;
use Doctrine\ORM\Mapping as ORM;

class UserRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;

    #[ORM\Column(name: "name", type: "string", length: 20, unique: true)]
    protected ?string $name = null;

    // methods
}
```

Schema:

```php
<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Entity\UserRole;
use OpenApi\Attributes as OA;

...

/**
 * @see UserRole
 */
#[OA\Schema(
    schema: 'UserRole',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'name', type: 'string', example: UserRole::ROLE_USER),
        new OA\Property(
            property: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/user/role/1234abcd-abcd-4321-12ab-123456abcdef',
                        ),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
```

Then, when generating the documentation file, `OpenAPI` will transform it into the specified format (**json**/**yaml**).

```yaml
UserRole:
  properties:
    uuid:
      type: string
      example: 1234abcd-abcd-4321-12ab-123456abcdef
    name:
      type: string
      example: user
    _links:
      properties:
        self:
          properties:
            href:
              type: string
              example: 'https://example.com/user/role/1234abcd-abcd-4321-12ab-123456abcdef'
          type: object
      type: object
  type: object
```

### Schemas describing collections of objects

Collections of objects are just as easy to describe in `OpenAPI` as they are in PHP.

PHP collection:

```php
<?php

declare(strict_types=1);

namespace Api\User\Collection;

use Api\App\Collection\ResourceCollection;

class UserRoleCollection extends ResourceCollection
{
}
```

Schema:

```php
#[OA\Schema(
    schema: 'UserRoleCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/UserRole',
                    ),
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Collection'),
    ],
)]
```

Using `ref: '#/components/schemas/UserRole',` in our code, we instruct `OpenAPI` to grab the existing schema `UserRole` (not the entity, but the schema) that we just described earlier.
This way we do not need to repeat code by describing again the same object, and any future modifications will happen in only one place.

Then, when generating the documentation file, `OpenAPI` will transform it into the specified format (**json**/**yaml**).

```php
UserRoleCollection:
  type: object
  allOf:
    -
      $ref: '#/components/schemas/Collection'
    -
      properties:
        _embedded:
          properties:
            roles:
              type: array
              items: { $ref: '#/components/schemas/UserRole' }
          type: object
      type: object
```

> Make sure that in `src/App/src/OpenAPI.php`, on the line with `#[OA\Server` the value of `url` is set to the of URL of your instance of **Dotkernel API**.
>
> You can add multiple servers (for staging, production, etc.) by duplicating the existing one.

For more info, see [this page](https://spec.openapis.org/oas/latest.html#schema).

### Common schemas

We provided some schemas that are reusable across the entire project. They are defined in `src/App/src/OpenAPI.php`:

- `#/components/schemas/Collection`: provides the default **HAL** structure to all the collections extending it
- `#/components/schemas/ErrorMessage`: describes an operation that resulted in an error—may contain multiple messages
- `#/components/schemas/InfoMessage`: describes an operation that completed successfully—may contain multiple messages
