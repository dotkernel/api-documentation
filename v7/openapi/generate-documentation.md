# Generating the documentation file

## Summary

How to run `composer openapi` to produce your OpenAPI file from the attributes in `src`, how its output location, OpenAPI version and format are configured, and when the underlying `zircote/swagger-php` command is an option instead.

## Details

> Make sure that `application.url` in `config/autoload/local.php` is set to the URL of your instance of **Dotkernel API**.
> The generator publishes it as the first entry of the `servers` list.

Using your terminal, move to the root directory of your project.

Dotkernel API stores the OpenAPI attributes in the `src` directory, and the document root (`info`, `servers`, tags and security schemes) in `config/autoload/openapi.global.php`.
Both are combined by `bin/generate-openapi.php`, which the `openapi` Composer script runs.

## Generating the file

```shell
composer openapi
```

This scans `src`, adds the document root from configuration and writes the result to the file named by `openapi.output_file`, `public/openapi.yaml` by default.
On success it prints the path of the file and the number of documented paths.

### Output location, OpenAPI version and format

These are configured, not passed as flags.
In `config/autoload/openapi.global.php`:

- `output_file`: where the file is written (default: `public/openapi.yaml`)
- `openapi_version`: the OpenAPI version, `3.1.0` by default (`3.0.0` is also supported)
- the format follows the extension of `output_file`: `.yaml` for YAML or `.json` for JSON

For example, to write JSON:

```php
'output_file' => 'public/openapi.json',
```

The output directory must already exist.
See [OpenAPI configuration](configuration.md) for every key.

### Using the `zircote/swagger-php` command directly

You can also run the underlying command:

```shell
./vendor/bin/openapi ./src --output public/openapi.yaml
```

This scans the attributes only.
The generated file will not contain the `info`, `servers`, tags or security schemes that `composer openapi` adds from configuration, so use it only when you need the raw output.
Its defaults differ: OpenAPI version `3.0.0` (override with `--version 3.1.0`) and the format taken from the output extension (force it with `--format json` or `--format yaml`).

## FAQ

**Q: What must I configure before generating?**

A: `application.url` in `config/autoload/local.php`, which has to point at your own instance.
Otherwise the generated file advertises the wrong server.

**Q: Where do the output location, version and format come from?**

A: From `config/autoload/openapi.global.php`: `output_file`, `openapi_version`, and the extension of `output_file`.
See [OpenAPI configuration](configuration.md).

**Q: Which OpenAPI versions can I generate?**

A: `3.1.0` (the configured default) and `3.0.0`, selected with `openapi_version`.

**Q: YAML or JSON?**

A: The extension of `output_file` decides: `.yaml` gives YAML, `.json` gives JSON.

**Q: Where should the generated file live?**

A: Anywhere your renderer can read it; `public/openapi.yaml` is the default, since it can then be served directly.
Point `output_file` outside `public` if the specification must not be served.
See [Render documentation](render-documentation.md).

**Q: Do I have to regenerate after changing annotations?**

A: Yes.
The file is a static snapshot, so re-run `composer openapi` whenever the attributes or the configuration change.

**Q: The command runs but my endpoint is missing. Why?**

A: Its class is most likely outside `src`, its attributes are incomplete, or its tag is listed in `exclude_tags`.
See [Write documentation](write-documentation.md) and [Getting help](getting-help.md).

**Q: Why does the generated file differ from what `./vendor/bin/openapi ./src` prints?**

A: The raw command knows nothing about the configuration.
`composer openapi` adds `info`, `servers`, tags and security schemes from `config/autoload/openapi.global.php`.
