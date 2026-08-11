# Generating the documentation file

## Summary

How to run `zircote/swagger-php` against the `src` directory to produce your OpenAPI file: printing it to the terminal, writing it to a chosen location, and selecting the OpenAPI version (`3.0.0` or `3.1.0`) and output format (`yaml` or `json`).

## Details

> Make sure that in `src/App/src/OpenAPI.php`, on the line with `#[OA\Server` the value of `url` is set to the of URL of your instance of **Dotkernel API**.

Using your terminal, move to the root directory of your project.

Dotkernel API stores the OpenAPI attributes in the `src` directory, so that's the path we will use for generating the static documentation file.

## Methods of generating a documentation file

### Without saving it to a file

```shell
./vendor/bin/openapi ./src
```

This will output the generated content to the terminal.

### Place it in a custom location

```shell
./vendor/bin/openapi ./src --output public/openapi.yaml
```

This will place the generated file `openapi.yaml` in the `public` directory.

### Specify OpenAPI version

Supported OpenAPI versions are `3.0.0` and `3.1.0`, `3.0.0` being the default version.

The below command will specify both the output location and the OpenAPI version:

```shell
./vendor/bin/openapi ./src --version 3.1.0
```

### Specify an output file format

Supported file formats are `yaml` and `json`, `yaml` being the default format.

The below command will specify the output location and `zircote/swagger-php` will determine the file format:

```shell
./vendor/bin/openapi ./src --output public/openapi.json
```

Or be specific about the format by appending the `--format` argument:

```shell
./vendor/bin/openapi ./src --output public/openapi.json --format json
```

These will place the generated file `openapi.json` in the `public` directory.

## FAQ

**Q: What must I configure before generating?**

A: The `url` value on the `#[OA\Server` line in `src/App/src/OpenAPI.php`, which has to point at your own instance.
Otherwise the generated file advertises the wrong server.

**Q: Why is `./src` the path passed to the command?**

A: Because Dotkernel API keeps its OpenAPI attributes in the `src` directory.
The generator scans that path for annotated classes.

**Q: Which OpenAPI versions can I generate?**

A: `3.0.0` (the default) and `3.1.0`, selected with `--version`.

**Q: YAML or JSON?**

A: YAML is the default.
Use a `.json` output filename and the generator picks JSON, or state it explicitly with `--format json`.

**Q: Where should the generated file live?**

A: Anywhere your renderer can read it; `public/openapi.yaml` is the common choice, since it can then be served directly.
See [Render documentation](render-documentation.md).

**Q: Do I have to regenerate after changing annotations?**

A: Yes.
The file is a static snapshot, so re-run the command whenever the annotations change.

**Q: The command runs but my endpoint is missing. Why?**

A: Its class is most likely outside the scanned path, or its attributes are incomplete.
See [Write documentation](write-documentation.md) and [Getting help](getting-help.md).
