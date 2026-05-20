# Architecture at a Glance

Dotkernel API follows a modular, middleware-based architecture designed for scalability and maintainability.
Understanding the core structure is essential before diving into development.

## The Core vs App Split (Since v6.0)

Since version 6.0, Dotkernel API is organized into two distinct layers: Core and App.

| Layer | Purpose                                                                                             | Items                                                                                | Location      |
|-------|-----------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------|---------------|
| Core  | The backbone of your application, the system-level infrastructure that handles fundamental concerns | Authentication & Authorization, Database, Common Services, Shared Entities           | src/Core/src/ |
| App   | The project-specific features, "business logic" of your application                                 | Routes, Handlers, Custom Services, Input Filters, Custom Middleware, Error Reporting | src/App/src/  |

## Architecture

Dotkernel API is built toward a **Headless Platform** architecture.
Out of the box, it is a modular monolith that can be split into modules and microservices.

```quote
┌──────────────────────────────────────────────────────────┐
│                    Multiple Frontends                    │
│        (Web, Mobile, Desktop, Voice, etc.)               │
└────────────┬────────────────────────────┬────────────────┘
             │                            │
             └────────────┬───────────────┘
                          │ (REST/JSON APIs)
┌─────────────────────────▼──────────────────────────────┐
│           Dotkernel API (Headless Backend)             │
│  ┌──────────────────────────────────────────────────┐  │
│  │ Core Layer (Infrastructure)                      │  │
│  │ • Authentication (OAuth2)                        │  │
│  │ • Authorization (RBAC)                           │  │
│  │ • Database (Doctrine ORM)                        │  │
│  │ • Middleware Pipeline                            │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────┐  │
│  │ App Layer (Business Logic)                       │  │
│  │ • Endpoints & Routes                             │  │
│  │ • Handlers & Services                            │  │
│  │ • Custom Logic                                   │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────┬──────────────────┘
                                      │
                    ┌─────────────────▼─────────────────┐
                    │   Database                        │
                    │ (MariaDB / PostgreSQL)            │
                    └───────────────────────────────────┘
```

## Modular Monolith Architecture

Applications are organized into modules, each handling a specific domain.

**Built-in Modules:**

- Admin: Admin account management (superuser/admin roles)
- User: Regular user management (user/guest roles)
- Security: OAuth2 token generation and refresh
- App: Error reporting and general endpoints
- Core: Shared infrastructure and base classes

Custom Modules: You can create your own modules (e.g., Book, Product, Article) following the same pattern.

### Request Flow

Here's how a typical request flows through Dotkernel API:

```quote
1. HTTP Request
   ↓
2. Middleware Pipeline (config/pipeline.php)
   ├─ CorsMiddleware (handle CORS)
   ├─ AuthenticationMiddleware (identify user)
   ├─ AuthorizationMiddleware (check permissions)
   ├─ ContentNegotiationMiddleware (validate Accept/Content-Type)
   └─ RouteMiddleware (match route)
   ↓
3. Handler (PSR-15 RequestHandler)
   ├─ Receive ServerRequestInterface
   ├─ Execute business logic
   └─ Return ResponseInterface
   ↓
4. Response Middleware (if any)
   ├─ ProblemDetailsMiddleware (handle exceptions)
   ├─ DeprecationMiddleware (add deprecation headers)
   └─ Response Header Middleware (set custom headers)
   ↓
5. HTTP Response
```

## Key Components

| Component         | Purpose                                                                                             | Example                                                                 | Notes                                                                            |
|-------------------|-----------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------|----------------------------------------------------------------------------------|
| Handlers (PSR-15) | Process incoming HTTP requests, coordinate application logic/services, and return the HTTP response | `GetUserResourceHandler.php`, `PostUserResourceHandler.php`             | Some of the benefits are: separation of concerns, easier testing, clearer intent |
| Services          | Contains the business logic layer that sits between the handlers and repositories                   | Business rules validation, Data transformation, Cross-cutting concerns  | Execution flow: `Handler → Service → Repository → Database`                      |
| Repositories      | Data access layer using Doctrine ORM                                                                | Query building, Entity persistence, Database abstraction                | The only component that interacts with the database                              | 
| Input Filters     | Filter and validate requests using Laminas InputFilter                                              | Login form, contact us form, `$_GET` and `$_POST` values, CLI arguments | Execution flow: `Request → InputFilter → Validation → Handler`                   |
| Entities          | Represent database tables using Doctrine ORM                                                        | `class User { ... }`                                                    | Ensure consistency between database and application data                         |

## Configuration Organization

```quote
config/
├─ config.php                     (Main entry point)
├─ pipeline.php                   (Middleware stack)
├─ container.php                  (Dependency injection)
└─ autoload/
    ├─ dependencies.global.php        (Service definitions)
    ├─ authorization.global.php       (RBAC rules)
    ├─ content-negotiation.global.php (Accept/Content-Type)
    ├─ doctrine.global.php            (ORM configuration)
    └─ local.php                      (Environment-specific, ignored by VCS, private config)
```

## Dependency Injection

Dotkernel API uses constructor injection with attributes:

```quote
use Dot\DependencyInjection\Attribute\Inject;

class UserHandler
{
    #[Inject(
        UserService::class,
        "config"
    )]
    public function __construct(
        protected UserService $userService,
        protected array $config
    ) {}
}
```

Services are automatically resolved and injected by AttributedServiceFactory.

## Data Flow Architecture

```quote
┌─────────────────────────────────────────┐
│    Incoming HTTP Request (PSR-7)        │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│    Middleware Pipeline                  │
│  (Auth, Validation, Negotiation)        │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│    Handler (Business Logic)             │
│  • Extract request data                 │
│  • Validate with InputFilter            │
│  • Call Service layer                   │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│    Service Layer (Domain Logic)         │
│  • Business rules                       │
│  • Data transformation                  │
│  • Call Repository layer                │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│    Repository Layer (Data Access)       │
│  • Doctrine queries                     │
│  • Entity persistence                   │
└────────────┬────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────┐
│    Database (MariaDB / PostgreSQL)      │
└─────────────────────────────────────────┘
```

## Standards & PSRs

Dotkernel API adheres to PHP standards for interoperability.
They ensure that your code can integrate with other PSR-compliant libraries.

| PSR and Specifications                       | Git Implementation                                                                                                                                                 | Level      | Description                                       |
|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------|---------------------------------------------------|
| [PSR-7](https://www.php-fig.org/psr/psr-7)   | [php-fig/http-message](https://github.com/php-fig/http-message)                                                                                                    | Core       | HTTP Message Interfaces (Requests/Responses)      |
| [PSR-11](https://www.php-fig.org/psr/psr-11) | [php-fig/container](https://github.com/php-fig/container)                                                                                                          | Core       | Container Interface (Dependency Injection)        |
| [PSR-15](https://www.php-fig.org/psr/psr-15) | [php-fig/http-server-handler](https://github.com/php-fig/http-server-handler), [php-fig/http-server-middleware](https://github.com/php-fig/http-server-middleware) | Core       | HTTP Handlers and Middleware (Request processing) |
| [PSR-3](https://www.php-fig.org/psr/psr-3)   | [php-fig/log](https://github.com/php-fig/log)                                                                                                                      | Supporting | Logger Interface (Requests/Responses)             |
| [PSR-4](https://www.php-fig.org/psr/psr-4)   | [php-fig/log](https://github.com/php-fig/log)                                                                                                                      | Supporting | Autoloading (File organization)                   |
| [PSR-6](https://www.php-fig.org/psr/psr-6)   | [php-fig/cache](https://github.com/php-fig/cache)                                                                                                                  | Supporting | Caching Interface                                 |
| [PSR-13](https://www.php-fig.org/psr/psr-13) | [php-fig/link](https://github.com/php-fig/link)                                                                                                                    | Supporting | Link Definition Interfaces                        |
| [PSR-14](https://www.php-fig.org/psr/psr-14) | [php-fig/event-dispatcher](https://github.com/php-fig/event-dispatcher)                                                                                            | Supporting | Event Dispatcher                                  |
| [PSR-17](https://www.php-fig.org/psr/psr-17) | [php-fig/http-factory](https://github.com/php-fig/http-factory)                                                                                                    | Supporting | HTTP Factories                                    |
| [PSR-18](https://www.php-fig.org/psr/psr-18) | [php-fig/http-client](https://github.com/php-fig/http-client)                                                                                                      | Supporting | HTTP Client                                       |
| [PSR-20](https://www.php-fig.org/psr/psr-20) | [php-fig/clock](https://github.com/php-fig/clock)                                                                                                                  | Supporting | Clock                                             |

> Supporting PSRs are installed by dependencies.

## Security Layers

```quote
┌─────────────────────────────┐
│  1. Authentication          │
│  (OAuth2 tokens)            │
├─────────────────────────────┤
│  2. Authorization           │
│  (RBAC permissions)         │
├─────────────────────────────┤
│  3. Input Validation        │
│  (InputFilter)              │
├─────────────────────────────┤
│  4. Content Negotiation     │
│  (Accept/Content-Type)      │
└─────────────────────────────┘
```

## When to Use Each Layer

| Layer      |         Purpose          |                              Example |
|------------|:------------------------:|-------------------------------------:|
| Core       |  System infrastructure   |       Authentication, database setup |
| App        |     Project features     |   User CRUD operations, custom logic |
| Handler    | Request/response mapping |        Extract user ID, call service |
| Service    |      Business rules      | Validate user data, calculate totals |
| Repository |       Data queries       |              Find users, save entity |
