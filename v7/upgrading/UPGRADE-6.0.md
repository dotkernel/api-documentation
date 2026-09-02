# Upgrading from 5.x to 6.0

## Summary

The changes you need to port into your project when moving from Dotkernel API 5.x to 6.0, each linked to the pull request that introduced it.
This release reorganised shared logic into the Core module, refactored handlers and services, introduced route grouping, and replaced Twig with a custom templating solution.

## Details

> You can find a complete list in [Changelog](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md)

* Move common logic to Core module [https://github.com/dotkernel/api/pull/358](https://github.com/dotkernel/api/pull/358)
* Refactored Handlers [https://github.com/dotkernel/api/pull/385](https://github.com/dotkernel/api/pull/385)
* Inject `InputFilters` in handlers [https://github.com/dotkernel/api/pull/389](https://github.com/dotkernel/api/pull/389)
* Implemented route grouping [https://github.com/dotkernel/api/pull/391](https://github.com/dotkernel/api/pull/391)
* Service refactoring [https://github.com/dotkernel/api/pull/396](https://github.com/dotkernel/api/pull/396)
* Autogenerate `OAuth2` keys when cloning the project [https://github.com/dotkernel/api/pull/398](https://github.com/dotkernel/api/pull/398)
* Refresh Postman documentation [https://github.com/dotkernel/api/pull/400](https://github.com/dotkernel/api/pull/400)
* Merge `Admin.Core` into `API.Core` [https://github.com/dotkernel/api/pull/401](https://github.com/dotkernel/api/pull/401)
* Implemented `mezzio/mezzio-problem-details` [https://github.com/dotkernel/api/pull/402](https://github.com/dotkernel/api/pull/402)
* Update pre-run.sh [https://github.com/dotkernel/api/pull/404](https://github.com/dotkernel/api/pull/404)
* Update `GetIndexResourceHandler.php` [https://github.com/dotkernel/api/pull/408](https://github.com/dotkernel/api/pull/408)
* Update `local.php.dist` [https://github.com/dotkernel/api/pull/409](https://github.com/dotkernel/api/pull/409)
* Fixed error handling [https://github.com/dotkernel/api/pull/412](https://github.com/dotkernel/api/pull/412)
* Implemented `ResourceProviderMiddleware` and added `ResourceGuardInterface` [https://github.com/dotkernel/api/pull/403](https://github.com/dotkernel/api/pull/403)
* Updated logic in `ContentNegotiationMiddleware` [https://github.com/dotkernel/api/pull/413](https://github.com/dotkernel/api/pull/413)
* `AuthenticationMiddleware` no longer extends `AuthenticationMiddleware` from `mezzio/mezzio-authentication` [https://github.com/dotkernel/api/pull/418](https://github.com/dotkernel/api/pull/418)
* Update `qodana_code_quality.yml` [https://github.com/dotkernel/api/pull/416](https://github.com/dotkernel/api/pull/416)
* Replaced `Twig` with custom templating solution [https://github.com/dotkernel/api/pull/419](https://github.com/dotkernel/api/pull/419)
* Increased `PHPStan` level to 8 [https://github.com/dotkernel/api/pull/421](https://github.com/dotkernel/api/pull/421)
* Split the `/security/token` endpoint into two separate endpoints [https://github.com/dotkernel/api/pull/423](https://github.com/dotkernel/api/pull/423)

## FAQ

**Q: Which change in 6.0 is most likely to break my code?**

A: Moving common logic into the Core module, together with the handler and service refactoring.
Namespaces and constructor signatures changed, so your own handlers and services need updating.
See [Core and App](../extended-features/core-and-app.md).

**Q: What replaced Twig for templating?**

A: A custom templating solution shipped with Dotkernel.
Templates that relied on Twig syntax need to be rewritten.
See [Rendering and sending emails](../core-features/rendering-and-sending-emails.md).

**Q: Why was the `/security/token` endpoint split in two?**

A: To separate issuing a token from refreshing one, which makes each endpoint's request and response contract explicit.
See [Token authentication](../tutorials/token-authentication.md).

**Q: What does "inject `InputFilters` in handlers" change for me?**

A: Input filters are now resolved from the container and injected rather than constructed inside the handler.
See [Injectable input filters](../extended-features/injectable-input-filters.md).

**Q: Do I need to regenerate my OAuth2 keys?**

A: Keys are generated automatically when cloning the project from 6.0 onward.
Existing installations can keep their current keys.
