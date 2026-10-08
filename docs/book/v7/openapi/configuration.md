# OpenAPI configuration

## Summary

The values that make up the root of the generated OpenAPI document — where the file is written, the OpenAPI version, `info`, `servers`, external documentation, tags and security schemes — are not attributes.
They live in `config/autoload/openapi.global.php` and `application.url`, and `bin/generate-openapi.php` reads them when you run `composer openapi`.

## Details

PHP attribute arguments must be constant expressions, so an `OA\Server` attribute cannot read the base URL out of your local config.
For that reason the document root is assembled by `bin/generate-openapi.php` from configuration after the scan of `src`, while paths and schemas stay in each module's `OpenAPI.php`.

The environment-independent keys belong in `config/autoload/openapi.global.php`, under the `openapi` key.
The values that differ per environment belong in your local config:

- `application.url` in `config/autoload/local.php`: the URL of your instance of **Dotkernel API**, published as the first entry of `servers` (use no trailing slash)
- `openapi.server_description` (optional): the label of that first server (example: `Dev`, `Staging` or `Production`)

## Configuration keys

### `output_file`

Where `composer openapi` writes the document.
Default: `public/openapi.yaml`.
The format follows the file extension: `.yaml` or `.json`.

`public/` is what makes the document reachable over HTTP.
If the specification should not be served, point this key outside it, for example `data/openapi.yaml`.
The output directory must already exist.

### `openapi_version`

The OpenAPI version of the generated document.
Default: `3.1.0`.

### `info`

Becomes the `OA\Info` object:

- `title`: title shown in the UI (default: `Dotkernel API`)
- `version`: API version (default: `1.0`)

### `external_docs`

Becomes the `OA\ExternalDocumentation` object, and is omitted when `url` is not set:

- `description`: describes the purpose of the document
- `url`: external documentation URL

### `servers`

Servers published after the project's own URL.
The first server is always `application.url`, labelled with `openapi.server_description`.
Each entry has a `url` and an optional `description`:

```php
'servers' => [
    ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
],
```

A URL that is already in the list is written once, and the first occurrence keeps its description.
An entry without a non-empty `url` makes `composer openapi` fail.

### `tags`

A map of tag name to description, published as the document's `tags` block.
Every tag you use in a module's `OpenAPI.php` should be declared here, so the renderer can describe the group.

```php
'tags' => [
    'Book' => 'Book records.',
],
```

### `exclude_tags`

A list of tags whose endpoints are left out of the generated document.
Both the paths and the schemas only they referenced disappear, and the names are also dropped from the `tags` block.

```php
'exclude_tags' => [
    'Book',
],
```

The endpoints keep working: this hides them from the document, it is not access control.
Match the tag exactly as the operations declare it, including spaces.

### `generated_timezone`

An IANA timezone identifier (default: `UTC`) used for `info.x-generated`, the ISO-8601 timestamp of when the document was built.
Set it to `null` to leave the field out, which also makes the output identical between runs, useful if you commit the document.

### `security_schemes`

A map of scheme name to definition, published as `OA\SecurityScheme` objects.
The shipped schemes are:

```php
'security_schemes' => [
    'AuthToken'           => [
        'type'          => 'http',
        'bearer_format' => 'JWT',
        'scheme'        => 'bearer',
    ],
    'ErrorReportingToken' => [
        'type' => 'apiKey',
        'name' => 'Error-Reporting-Token',
        'in'   => 'header',
    ],
],
```

Each definition accepts `type`, `name`, `in`, `bearer_format` and `scheme`.

## FAQ

**Q: Where do I set the server URL?**

A: In `application.url`, in `config/autoload/local.php`.
The generator publishes it as the first server.
See [Generate documentation](generate-documentation.md).

**Q: Why are these values not attributes?**

A: Attribute arguments must be constant expressions, so they cannot read the base URL from local config.
The generator builds the document root from configuration instead.

**Q: How do I publish more than one server?**

A: Add entries to `openapi.servers`, each with a `url` and an optional `description`.

**Q: How do I stop publishing a module's endpoints?**

A: List its tag in `exclude_tags` and run `composer openapi` again.
The endpoints keep working; they are only hidden from the document.

**Q: How do I make the output reproducible between runs?**

A: Set `generated_timezone` to `null`, so no build timestamp is written.

**Q: Where do the components these keys produce show up?**

A: See [Initialized components](initialized-components.md) for how each one is described.
