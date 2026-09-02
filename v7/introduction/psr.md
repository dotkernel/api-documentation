# PSRs

## Summary

Dotkernel API is built on the PHP-FIG standards, which keep your code portable and let PSR-compliant libraries drop in without adapters.
PSR-7 (HTTP messages), PSR-15 (handlers and middleware) and PSR-11 (container) are architectural foundations; PSR-3, PSR-4, PSR-6, PSR-13, PSR-14, PSR-17, PSR-18 and PSR-20 arrive through dependencies.

## Why PSRs Matter for Dotkernel API

- **Vendor Lock-In Prevention**: By following PSRs, you're not locked into Dotkernel API. Your code can be reused in other PSR-compliant frameworks.
- **Ecosystem Compatibility**: Any library that follows PSRs can integrate with Dotkernel API without custom adapters.
- **Team Collaboration**: Developers familiar with PSRs can immediately understand Dotkernel API's code structure.
- **Long-Term Maintenance**: PSRs are stable standards maintained by the PHP community, ensuring longevity.
- **Code Quality**: Following standards encourages best practices and makes code more maintainable.

## PHP Standards Recommendations (PSRs)

Dotkernel API adheres to PHP Standards Recommendations (PSRs) established by the PHP-FIG (Framework Interoperability Group). These standards ensure code interoperability and allow Dotkernel API to work seamlessly with other PSR-compliant libraries.

Some PSRs are at the **core** of Dotkernel API's architecture, while others are installed as dependencies through third-party packages.

## Core PSRs (Essential to Dotkernel API)

### PSR-7: HTTP Message Interfaces

**Repository**: [php-fig/http-message](https://github.com/php-fig/http-message)

Defines standardized interfaces for HTTP messages (requests and responses) and URIs.

**Why it matters**:

- Dotkernel API uses PSR-7 for all HTTP communication.
- Ensures requests and responses follow a universal standard.
- Allows middleware and handlers to work with consistent interfaces.
- Implemented via `Laminas\Diactoros`.

### PSR-15: HTTP Server Request Handlers and Middleware

**Repository**: [php-fig/http-server-handler](https://github.com/php-fig/http-server-handler) and [php-fig/http-server-middleware](https://github.com/php-fig/http-server-middleware)

Defines the interface for HTTP request handlers and middleware components that process PSR-7 HTTP messages.

**Why it matters**:

- Dotkernel API's entire architecture is built on PSR-15.
- All handlers implement `RequestHandlerInterface`.
- Middleware pipeline processes requests in a chain.
- Single-action handlers follow this pattern for clean separation of concerns.

### PSR-11: Container Interface

**Repository**: [php-fig/container](https://github.com/php-fig/container)

Defines the standard interface for dependency injection containers.

**Why it matters**:

- Dotkernel API uses PSR-11 for managing service dependencies.
- All services are registered in and retrieved from a container.
- Enables loose coupling between components.
- Implemented via `Laminas\ServiceManager`.

## Supporting PSRs (Installed via Dependencies)

### PSR-3: Logger Interface

**Repository**: [php-fig/log](https://github.com/php-fig/log)

Provides a standard interface for logging libraries.

**Usage**: Error handling, debugging, audit trails
**Implemented in**: `dotkernel/dot-errorhandler`

### PSR-4: Autoloader

**Repository**: [php-fig/log](https://github.com/php-fig/log)

Defines how PHP files are automatically loaded based on namespaces and file paths.

**Usage**: Automatic class loading without manual `require` statements
**Implemented in**: `Laminas\Loader`

### PSR-6: Caching Interface

**Repository**: [php-fig/cache](https://github.com/php-fig/cache)

Defines standard interfaces for caching systems to improve application performance.

**Usage**: Caching query results, configuration, templates
**Implemented in**: `dotkernel/dot-cache`

### PSR-13: Link Definition Interfaces

**Repository**: [php-fig/link](https://github.com/php-fig/link)

Describes how to represent hypermedia links independently of serialization format.

**Usage**: HAL (Hypertext Application Language) resource links
**Implemented in**: `mezzio/mezzio-hal`

### PSR-14: Event Dispatcher

**Repository**: [php-fig/event-dispatcher](https://github.com/php-fig/event-dispatcher)

Mechanism for event-based extension and collaboration between components.

**Usage**: Triggering events on user actions, logging events, notifications
**Implemented in**: Third-party packages as needed

### PSR-17: HTTP Factories

**Repository**: [php-fig/http-factory](https://github.com/php-fig/http-factory)

Standard for factories that create PSR-7 compliant HTTP objects.

**Usage**: Creating requests, responses, and streams programmatically
**Implemented in**: `Laminas\Diactoros`

### PSR-18: HTTP Client

**Repository**: [php-fig/http-client](https://github.com/php-fig/http-client)

Interface for sending HTTP requests and receiving HTTP responses.

**Usage**: Calling external APIs from your Dotkernel API
**Implemented in**: `symfony/http-client` or similar packages

### PSR-20: Clock

**Repository**: [php-fig/clock](https://github.com/php-fig/clock)

Provides a standard interface for reading the system clock.

**Usage**: Getting current time in a testable way
**Implemented in**: Third-party packages as needed

### PSR Implementation Hierarchy

```quote
┌───────────────────────────────────────────┐
│  PSR-7: HTTP Messages (Requests/Responses)│
└───────────────────────────────────────────┘
                    ▲
                    │
┌───────────────────────────────────────────┐
│ PSR-15: Handlers & Middleware             │
│ (Process PSR-7 messages)                  │
└───────────────────────────────────────────┘
                    ▲
                    │
┌───────────────────────────────────────────┐
│ PSR-11: Container                         │
│ (Manages services for handlers)           │
└───────────────────────────────────────────┘
                    ▲
                    │
┌───────────────────────────────────────────┐
│ PSR-4: Autoloader                         │
│ (Loads services automatically)            │
└───────────────────────────────────────────┘
```

## FAQ

**Q: Which PSRs are essential to the architecture?**

A: PSR-7, PSR-15 and PSR-11.
Everything else is supporting, arriving through dependencies rather than shaping the design.

**Q: What does building on PSRs buy me in practice?**

A: Portability.
Because your handlers and services depend on standard interfaces rather than framework classes, they can be reused in any other PSR-compliant framework, and third-party PSR libraries integrate without custom adapters.

**Q: What implements PSR-7 here?**

A: `Laminas\Diactoros`, which also provides the PSR-17 HTTP factories.

**Q: How does PSR-15 show up in the code I write?**

A: Every handler implements `RequestHandlerInterface` and serves a single action, and requests travel through a middleware pipeline.
See [The new handler structure](../extended-features/handler-structure.md).

**Q: Which container is used?**

A: `Laminas\ServiceManager`, behind the PSR-11 interface.
See [Dependency injection](../core-features/dependency-injection.md).

**Q: Which PSR governs the file layout?**

A: PSR-4 autoloading, which maps namespaces to paths.
See [File structure](file-structure.md).

**Q: Do I need to install anything to use PSR-3 logging or PSR-6 caching?**

A: No. They come with `dotkernel/dot-errorhandler` and `dotkernel/dot-cache` respectively.
See [Packages](packages.md).

**Q: How do I call an external API from Dotkernel API?**

A: Through a PSR-18 HTTP client such as `symfony/http-client`, so your calling code depends on the interface rather than a specific client.

**Q: Are PSR-14 events and PSR-20 clock available out of the box?**

A: Not by default.
Both are supplied by third-party packages when a project needs them.
