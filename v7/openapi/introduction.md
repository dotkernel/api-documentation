# OpenAPI documentation

## Summary

Dotkernel API ships with `zircote/swagger-php`, letting you describe endpoints with PHP attributes and auto-generate an interactive OpenAPI specification from them.

## Details

To provide an interactive documentation, Dotkernel API implemented [zircote/swagger-php](https://github.com/zircote/swagger-php).

Developers can use this library to auto-generate documentation that outlines available endpoints, their request details, and their response formats.

> The pages of this section describe Dotkernel API 7.2, where `composer openapi` generates the file from configuration.
> Versions 7.0 and 7.1 have no `composer openapi` script or `openapi.global.php`; see the version differences in [Generate documentation](generate-documentation.md#version-differences).
> Update to the latest Dotkernel API 7.x release, see [Upgrading from 7.1 to 7.2](../upgrading/UPGRADE-7.2.md).

## FAQ

**Q: What is OpenAPI?**

A: OpenAPI is a standard, machine-readable format for describing HTTP APIs.
Tools can consume the specification to render documentation, generate clients, or drive tests.

**Q: Do I write the specification by hand?**

A: No.
You annotate your handlers and models with `zircote/swagger-php` attributes, then generate the specification file from them.
See [Write documentation](write-documentation.md).

**Q: Which OpenAPI version is used?**

A: `3.1.0` by default, set with `openapi_version` in `config/autoload/openapi.global.php`; `3.0.0` is also supported.
Consult the [OpenAPI specification](https://spec.openapis.org/oas/latest.html) for the object reference.

**Q: How do I view the generated documentation?**

A: Generate the specification, then serve it through a renderer.
Run `composer openapi` to generate it, then serve it through a renderer.
See [Generate documentation](generate-documentation.md), [OpenAPI configuration](configuration.md) and [Render documentation](render-documentation.md).
