# Getting help

## Summary

Where to look when an OpenAPI annotation does not behave as expected: the specification itself, `zircote/swagger-php`'s examples and documentation, and the library's built-in help output.

## Details

- consult the OpenAPI [specs](https://spec.openapis.org/oas/latest.html) for a complete reference of the presented objects
- see more examples of OpenAPI object representations in `zircote/swagger-php`'s [GitHub repository](https://zircote.github.io/swagger-php/guide/examples.html)
- consult `zircote/swagger-php`'s [online documentation](http://zircote.github.io/swagger-php/guide/generating-openapi-documents.html) or run the following command to see their help page:

```shell
./vendor/bin/openapi --help
```

## FAQ

**Q: My annotation is ignored in the generated output. What should I check first?**

A: Check that the annotated class is inside a path scanned by the generator, and that the attribute is spelled and nested correctly.
Running the generator usually reports the offending file.

**Q: Where do I find the meaning of a specific OpenAPI object?**

A: In the [OpenAPI specification](https://spec.openapis.org/oas/latest.html).
It is the authoritative reference for every object and field name.

**Q: How do I see the available generator options?**

A: Run `./vendor/bin/openapi --help`.

**Q: Should I report annotation problems to Dotkernel or to swagger-php?**

A: If the problem is in how Dotkernel API is annotated or configured, report it to Dotkernel.
If the generator itself misbehaves, report it upstream to `zircote/swagger-php`.
