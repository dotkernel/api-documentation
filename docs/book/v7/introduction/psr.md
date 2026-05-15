# PSRs

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
