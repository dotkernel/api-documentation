# Authorization

## Summary

Authorization decides whether an already-authenticated identity may reach a given resource.
Dotkernel API implements it with role-based access control through `Mezzio\Authorization\Rbac\LaminasRbac`, applied by `AuthorizationMiddleware` and configured in `config/autoload/authorization.global.php`, where each permission is a route name.
Inheritance runs from child to parent, so `superuser` inherits every permission granted to `admin` without declaring any of its own.

## Details

Authorization is the process by which a system takes a validated identity and checks if that identity has access to a given resource.

**Dotkernel API**'s implementation of authorization uses `Mezzio\Authorization\Rbac\LaminasRbac` as a model of Role-Based Access Control (RBAC).

## How it works

In Dotkernel API each authenticatable entity (admin/user) has its own role table — `admin_role` and `user_role` — plus a join table, `admin_roles` and `user_roles`, assigning roles to accounts.
RBAC comes in to ensure that each entity has the appropriate role and permission to access a resource.

The authorization happens through the `Api\App\Middleware\AuthorizationMiddleware` middleware.

## Configuration

Dotkernel API makes use of `mezzio-authorization-rbac` and includes the full configuration.

The configuration file for the role and permission definitions is `config/autoload/authorization.global.php`.

Roles are the backed enums `Core\Admin\Enum\AdminRoleEnum` (`superuser`, `admin`) and `Core\User\Enum\UserRoleEnum` (`user`, `guest`), so the array keys are their `->value` strings.

```php
use Core\Admin\Enum\AdminRoleEnum;
use Core\User\Enum\UserRoleEnum;

return [
    'mezzio-authorization-rbac' => [
        'roles'       => [
            AdminRoleEnum::Superuser->value => [],
            AdminRoleEnum::Admin->value     => [
                AdminRoleEnum::Superuser->value,
            ],
            UserRoleEnum::Guest->value      => [
                UserRoleEnum::User->value,
            ],
        ],
        'permissions' => [
            AdminRoleEnum::Superuser->value => [],
            AdminRoleEnum::Admin->value     => [
                'admin::list-admin',
                'admin::create-admin',
                'admin::delete-admin',
                'admin::view-admin',
                'admin::update-admin',
                'admin::list-role',
                'admin::view-role',
                'admin::view-account',
                'admin::update-account',
                'user::list-user',
                'user::create-user',
                'user::delete-user',
                'user::view-user',
                'user::update-user',
                'user::delete-user-avatar',
                'user::view-user-avatar',
                'user::create-user-avatar',
                'user::list-role',
                'user::view-role',
                'user::activate-user',
                'user::deactivate-user',
                'app::create-error-report',
                'app::view-index',
            ],
            UserRoleEnum::User->value       => [
                'user::delete-account',
                'user::view-account',
                'user::update-account',
                'user::delete-account-avatar',
                'user::view-account-avatar',
                'user::create-account-avatar',
            ],
            UserRoleEnum::Guest->value      => [
                'app::create-error-report',
                'app::view-index',
                'user::activate-account',
                'user::request-activate-account',
                'user::recover-account',
                'user::check-account-reset-password',
                'user::update-account-reset-password',
                'user::create-account-reset-password',
                'user::create-account',
                'security::generate-token',
                'security::refresh-token',
            ],
        ],
    ],
];
```

That is the complete shipped configuration, not an excerpt.
Between them the three populated roles grant **38 permissions covering all 38 routes**: every route the application declares is reachable by at least one role, and no permission names a route that does not exist.
Only `app::view-index` and `app::create-error-report` are granted twice, to both `admin` and `guest`.

> See [mezzio-authorization-rbac](https://docs.mezzio.dev/mezzio-authorization-rbac/v1/basic-usage/) for more information.

## Usage

Based on the configuration file above, we have two admin roles (`superuser`, `admin`) and two user roles (`user`, `guest`).

A permission in Dotkernel API is a **route name** — the third argument given to the route in a module's `RoutesDelegator`.
To list the names you can grant, run `php ./bin/cli.php route:list`; see [Displaying Dotkernel API endpoints](../commands/display-available-endpoints.md).

### How inheritance works here

The array under `roles` maps a role to its **parents**, and inheritance runs in the direction that often surprises people: a **parent receives the permissions of its children**, because `laminas-permissions-rbac` resolves `hasPermission()` by walking down into child roles.

So in the shipped configuration:

| Entry | Meaning |
| --- | --- |
| `superuser => []` | `superuser` has no parent |
| `admin => [superuser]` | `superuser` is the parent of `admin`, so **`superuser` inherits everything granted to `admin`** |
| `guest => [user]` | `user` is the parent of `guest`, so **`user` inherits everything granted to `guest`** |

That is why `superuser` needs no permissions of its own: its list is empty, yet it can reach all 23 routes granted to `admin`.

It is also why `user` ends up with 17 effective permissions — its own 6 plus the 11 granted to `guest` — while `guest` keeps only its own 11 and cannot reach the account routes reserved for a signed-in user.

Effective totals, once inheritance is applied:

| Role | Own | Inherited | Effective |
| --- | --- | --- | --- |
| `superuser` | 0 | 23 from `admin` | 23 |
| `admin` | 23 | — | 23 |
| `user` | 6 | 11 from `guest` | 17 |
| `guest` | 11 | — | 11 |

### How a request is authorized

`AuthorizationMiddleware` injects `Mezzio\Authorization\AuthorizationInterface` rather than an RBAC class directly — the RBAC adapter is bound by `Mezzio\Authorization\Rbac\ConfigProvider`, registered in `config/config.php`.

For each request it:

1. Reads `oauth_client_id` from the authenticated identity and loads the matching record — `admin` from the `admin` table, `frontend` from the `user` table, or a `Guest` instance when the client is `guest`.
   An unrecognised client is rejected.
2. Rejects an account that is inactive, or a user that has been deleted.
3. Replaces the identity's roles with the role names read from that record.
4. Calls `isGranted()` once per role and allows the request as soon as **any** role grants the route.

If no role grants it, the response is `403 Forbidden` with `You are not allowed to access this resource.`

> Note this middleware returns a plain JSON error body rather than a Problem Details document, so an authorization failure does not look like the errors described in [Problem details](../extended-features/problem-details.md).

## FAQ

**Q: How does authorization differ from authentication?**

A: Authentication establishes who the caller is; authorization checks what that established identity is allowed to do.
See [Authentication](authentication.md).

**Q: What exactly is a permission in Dotkernel API?**

A: A route name.
Granting a role a permission means granting it access to the route of that name.

**Q: Where do I add permissions for a route I just created?**

A: To the relevant role's array in `config/autoload/authorization.global.php`.
A route with no permission entry is unreachable for that role.

**Q: Which access control model is used?**

A: RBAC, via `mezzio-authorization-rbac` backed by `laminas-permissions-rbac`.
`AuthorizationMiddleware` depends only on `Mezzio\Authorization\AuthorizationInterface`, so the adapter is selected by configuration rather than hardcoded.

**Q: How does role inheritance work here?**

A: The values listed against a role are its parents, and a parent inherits from its children — `laminas-permissions-rbac` resolves a permission by walking down into child roles.
Because `admin` lists `superuser`, `superuser` receives everything granted to `admin`, which is why `superuser` needs no permissions of its own.
Likewise `guest` lists `user`, so `user` inherits the guest permissions on top of its own.

**Q: Where are roles stored?**

A: In `admin_role` and `user_role`, with `admin_roles` and `user_roles` as the join tables that assign them to accounts.
The role names themselves come from the `AdminRoleEnum` and `UserRoleEnum` backed enums, so adding a role means adding an enum case as well as a row.

**Q: Which middleware enforces this?**

A: `Api\App\Middleware\AuthorizationMiddleware`.
See [Middleware flow](../flow/middleware-flow.md).

**Q: Can I use ACL instead of RBAC?**

A: The ACL adapter ships with the project, but RBAC is what Dotkernel API is configured for; switching means replacing the authorization configuration.
Because the middleware only knows `AuthorizationInterface`, no application code needs to change.

**Q: Do the permissions cover every route?**

A: Yes, exactly. The three populated roles grant 38 permissions across the 38 declared routes, with no route ungranted and no permission naming a route that does not exist.
`app::view-index` and `app::create-error-report` are the only two granted to two roles.

**Q: What does a rejected request look like?**

A: `403 Forbidden` with `You are not allowed to access this resource.`
The same status is returned when the account is inactive, the user was deleted, or the OAuth client is unrecognised, each with its own message.
