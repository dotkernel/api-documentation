# Architecture at a Glance

Dotkernel API follows a modular, middleware-based architecture designed for scalability and maintainability.
Understanding the core structure is essential before diving into development.

## The Core vs App Split (Since v6.0)

Since version 6.0, Dotkernel API is organized into two distinct layers:

### Core Layer

The **Core** is the backbone of your application—system-level infrastructure that handles fundamental concerns:

- Authentication & Authorization: OAuth2-based authentication with RBAC (Role-Based Access Control)
- Database Setup: Doctrine ORM configuration, entity definitions, and repositories
- Middleware Pipeline: Request/response processing, error handling, content negotiation
- Common Services: Mail service, error reporting, caching
- Shared Entities: Admin/User entities, roles, permissions

Location: `src/Core/src/`

You typically don't modify Core unless you're updating system behavior or adding shared infrastructure features.

### App Layer

The **App** is where you build your project-specific features—the "business logic" of your application:

- Routes: Endpoint definitions specific to your use case
- Handlers: PSR-15 request handlers (like controllers, but single-action focused)
- Custom Services: Business logic and data processing
- Input Filters: Request validation rules
- Custom Middleware: Application-specific middleware
- Error Reporting: Frontend error collection endpoints

Location: `src/App/src/`

You spend most development time here, implementing your API's features and business logic.

## Headless CMS Architecture

Dotkernel API is built toward a Headless CMS architecture:

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

## Modular Design

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

### Handlers (PSR-15)

Single-action request handlers instead of multi-action controllers:

```quote
src/User/src/Handler/
├─ GetUserCollectionHandler.php      (GET /user)
├─ GetUserResourceHandler.php        (GET /user/{id})
├─ PostUserResourceHandler.php       (POST /user)
├─ PatchUserResourceHandler.php      (PATCH /user/{id})
└─ DeleteUserResourceHandler.php     (DELETE /user/{id})
```

Benefits: Separation of concerns, easier testing, clearer intent.

### Services

The Business logic layer sits between the handlers and repositories:

```quote
Handler → Service → Repository → Database
```

Services handle:

- Business rules validation
- Data transformation
- Cross-cutting concerns (caching, logging)

### Repositories

Data access layer using Doctrine ORM:

- Query building
- Entity persistence
- Database abstraction

### Input Filters

Request validation using Laminas InputFilter:

```quote
Request → InputFilter → Validation → Handler
```

### Entities

Doctrine ORM entities representing database tables:

```php
#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User { ... }
```

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

Dotkernel API adheres to PHP standards for interoperability:

- **PSR-7**: HTTP Message Interfaces (Requests/Responses)
- **PSR-11**: Container Interface (Dependency Injection)
- **PSR-15**: HTTP Handlers and Middleware (Request processing)
- **PSR-4**: Autoloading (File organization)

This ensures your code can integrate with other PSR-compliant libraries.

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
