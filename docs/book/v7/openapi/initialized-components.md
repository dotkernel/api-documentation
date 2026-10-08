# Initialized OpenAPI components

## Summary

The OpenAPI components Dotkernel API already defines: `OA\Info` for API metadata, `OA\Server` for instance URLs, `OA\SecurityScheme` for the `AuthToken` and `ErrorReportingToken` headers and `OA\ExternalDocumentation`, all built from configuration, plus reusable schemas declared as attributes in the modules' `OpenAPI.php` files.
It also shows how to turn an entity or a collection into an `OA\Schema` and reference it with `ref` instead of repeating the definition.

## Details

Below you will find details on some prepopulated OpenAPI components we added to Dotkernel API.

> `OA\Info`, `OA\Server`, `OA\SecurityScheme` and `OA\ExternalDocumentation` are not declared as attributes.
> `bin/generate-openapi.php` builds them from `config/autoload/openapi.global.php` and `application.url` when you run `composer openapi`, and adds them to the document after the scan of `src`.
> See [OpenAPI configuration](configuration.md) for every key.

## OA\Info

Built from the `openapi.info` key in `config/autoload/openapi.global.php`, this object provides general info about the API:

- `version`: API version (default: `1.0`)
- `title`: title shown in the UI (default: `Dotkernel API`)

The generator also adds `x-generated` to this object: the time the document was built, in the timezone set by `openapi.generated_timezone`.
Set that key to `null` to omit it.

For more info, see [this page](https://spec.openapis.org/oas/latest.html#info-object).

## OA\Server

Built from `application.url` and the optional `openapi.server_description`, this object provides API server entries:

- `url`: API server URL (example: `https://api.example.com` - use no trailing slash!)
- `description`: describes the purpose of the server (example: `Dev`, `Staging`, `Production` or even `Auth` if you use a separate authentication server)

The first server is always `application.url`.
You can publish more servers, one for each of your Dotkernel API instances, with the `openapi.servers` key.

For more info, see [this page](https://spec.openapis.org/oas/latest.html#server-object).

## OA\SecurityScheme

Built from the `openapi.security_schemes` key, you will find an object for the `AuthToken` security scheme:

- `securityScheme`: `AuthToken`—the name you provide in an endpoint's `security` to indicate that it is protected
- `type`: `http`
- `scheme`: `bearer`
- `bearerFormat`: `JWT`, a hint to the client to identify how the bearer token is formatted

And another object for the `ErrorReportingToken` security token:

- `securityScheme`: `ErrorReportingToken`—the name you provide in an endpoint's `security` to indicate that it is protected
- `type`: `apiKey`
- `in`: `header`, where the scheme is applied
- `name`: `Error-Reporting-Token`, the name of the header

For more info, see [this page](https://spec.openapis.org/oas/latest.html#security-scheme-object).

## OA\ExternalDocumentation

Built from the `openapi.external_docs` key, in this object we provide the following details:

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

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Core\App\Entity\UuidIdentifierTrait;
use Core\User\Enum\UserRoleEnum;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ORM\Table(name: 'user_role')]
class UserRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;
    use UuidIdentifierTrait;

    #[ORM\Column(
        name: 'name',
        type: 'user_role_enum',
        unique: true,
        enumType: UserRoleEnum::class,
        options: ['default' => UserRoleEnum::User]
    )]
    protected UserRoleEnum $name = UserRoleEnum::User;

    // methods
}
```

Schema:

```php
<?php

declare(strict_types=1);

namespace Api\User;

use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use OpenApi\Attributes as OA;

...

/**
 * @see UserRole
 */
#[OA\Schema(
    schema: 'UserRole',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'name', type: 'string', example: UserRoleEnum::User->value),
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
    id:
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

```yaml
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

> Make sure that `application.url` in `config/autoload/local.php` is set to the URL of your instance of **Dotkernel API**.
>
> You can publish multiple servers (for staging, production, etc.) with the `openapi.servers` config key.

For more info, see [this page](https://spec.openapis.org/oas/latest.html#schema).

### Common schemas

We provided some schemas that are reusable across the entire project.
The general ones are defined in `src/App/src/OpenAPI.php`:

- `#/components/schemas/Collection`: provides the default **HAL** structure to all the collections extending it
- `#/components/schemas/ErrorMessage`: describes an operation that resulted in an error—may contain multiple messages
- `#/components/schemas/InfoMessage`: describes an operation that completed successfully—may contain multiple messages
- `#/components/schemas/HomeMessage`: describes the output of the API home page
- `#/components/schemas/DateTimeObject`: describes how a timestamp appears in responses (`date`, `timezone_type` and `timezone`)

The token endpoints have their own, defined in `src/Security/src/OpenAPI.php`:

- `#/components/schemas/OAuth2SuccessMessage`: the response of a successful token generation or refresh (`token_type`, `expires_in`, `access_token` and `refresh_token`)
- `#/components/schemas/OAuth2GenerateErrorMessage`: the error response of a failed token generation
- `#/components/schemas/OAuth2RefreshErrorMessage`: the error response of a failed token refresh—it adds a `hint` to the generation error

## FAQ

**Q: Where are these components defined?**

A: `OA\Info`, `OA\Server`, `OA\SecurityScheme` and `OA\ExternalDocumentation` are built from `config/autoload/openapi.global.php` and `application.url` by `bin/generate-openapi.php`.
The reusable schemas are attributes in the modules' `OpenAPI.php` files, mainly `src/App/src/OpenAPI.php`.
See [OpenAPI configuration](configuration.md).

**Q: What must I edit before generating documentation?**

A: `application.url` in `config/autoload/local.php`, which has to point at your own instance and must not have a trailing slash.

**Q: Can I document more than one environment?**

A: Yes.
Add one entry per instance to `openapi.servers` and use `description` to label each as `Dev`, `Staging`, `Production` or similar.

**Q: Which security schemes are predefined?**

A: `AuthToken` for the OAuth2 bearer token and `ErrorReportingToken` for the `Error-Reporting-Token` header of `/error-report`.
Naming one in an endpoint's `security` parameter marks that endpoint as protected.

**Q: What is the difference between an entity and a schema?**

A: The entity is the PHP class Doctrine maps to a table; the schema is the OpenAPI description of how that object appears in requests and responses.
You write the schema separately, referencing the entity with a `@see` annotation.

**Q: How do I avoid describing the same object twice?**

A: Reference the existing schema with `ref: '#/components/schemas/UserRole'`.
Later changes then happen in one place only.

**Q: How do I describe a collection?**

A: Define a schema whose `_embedded` property holds an array of `OA\Items` referencing the item schema, and combine it with `#/components/schemas/Collection` through `allOf`.

**Q: What do the shipped common schemas provide?**

A: `Collection` gives collections their default HAL structure, while `ErrorMessage` and `InfoMessage` describe failed and successful operations, each able to carry several messages.
`HomeMessage` and `DateTimeObject` describe the home page output and timestamps, and the `OAuth2…Message` schemas describe the token endpoints' responses.

**Q: Where do I look up the fields of an OpenAPI object?**

A: In the [OpenAPI specification](https://spec.openapis.org/oas/latest.html), linked per object throughout this page.
