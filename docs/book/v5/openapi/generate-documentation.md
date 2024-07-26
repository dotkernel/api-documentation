# Generating the documentation file

> Make sure that in `src/App/src/OpenAPI.php`, on the line with `#[OA\Server` the value of `url` is set to the of URL of
> your instance of **Dotkernel API**.

Using your terminal, move to the root directory of your project.

Dotkernel API stores the OpenAPI attributes in the `src` directory, so that's the path we will use for generating the
static documentation file.

## Methods of generating documentation file

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

### Specify output file format

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
