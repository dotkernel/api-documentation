# Upgrading from 5.x to 6.0

## Summary

The changes you need to port into your project when moving from Dotkernel API 5.x to 6.0, each linked to the pull request that introduced it.
This release reorganised shared logic into the Core module, refactored handlers and services, introduced route grouping, and replaced Twig with a custom templating solution.

## Details

> You can find a complete list in [Changelog](https://github.com/dotkernel/api/blob/7.0/CHANGELOG.md)

* [#358](https://github.com/dotkernel/api/pull/358): Move common logic to Core module
* [#385](https://github.com/dotkernel/api/pull/385): Refactored Handlers
* [#389](https://github.com/dotkernel/api/pull/389): Inject `InputFilters` in handlers
* [#391](https://github.com/dotkernel/api/pull/391): Implemented route grouping
* [#396](https://github.com/dotkernel/api/pull/396): Service refactoring
* [#398](https://github.com/dotkernel/api/pull/398): Autogenerate `OAuth2` keys when cloning the project
* [#400](https://github.com/dotkernel/api/pull/400): Refresh Postman documentation
* [#401](https://github.com/dotkernel/api/pull/401): Merge `Admin.Core` into `API.Core`
* [#402](https://github.com/dotkernel/api/pull/402): Implemented `mezzio/mezzio-problem-details`
* [#404](https://github.com/dotkernel/api/pull/404): Update pre-run.sh
* [#408](https://github.com/dotkernel/api/pull/408): Update `GetIndexResourceHandler.php`
* [#409](https://github.com/dotkernel/api/pull/409): Update `local.php.dist`
* [#412](https://github.com/dotkernel/api/pull/412): Fixed error handling
* [#403](https://github.com/dotkernel/api/pull/403): Implemented `ResourceProviderMiddleware` and added `ResourceGuardInterface`
* [#413](https://github.com/dotkernel/api/pull/413): Updated logic in `ContentNegotiationMiddleware`
* [#418](https://github.com/dotkernel/api/pull/418): `AuthenticationMiddleware` no longer extends `AuthenticationMiddleware` from `mezzio/mezzio-authentication`
* [#416](https://github.com/dotkernel/api/pull/416): Update `qodana_code_quality.yml`
* [#419](https://github.com/dotkernel/api/pull/419): Replaced `Twig` with custom templating solution
* [#421](https://github.com/dotkernel/api/pull/421): Increased `PHPStan` level to 8
* [#423](https://github.com/dotkernel/api/pull/423): Split the `/security/token` endpoint into two separate endpoints

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
