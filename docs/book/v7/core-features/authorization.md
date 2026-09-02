# Authorization

## Summary

Authorization decides whether an already-authenticated identity may reach a given resource.
Dotkernel API implements it with role-based access control through `Mezzio\Authorization\Rbac\LaminasRbac`, applied by `AuthorizationMiddleware` and configured in `config/autoload/authorization.global.php`, where each permission is a route name and roles inherit from their parents.

## Details

Authorization is the process by which a system takes a validated identity and checks if that identity has access to a given resource.

**Dotkernel API**'s implementation of authorization uses `Mezzio\Authorization\Rbac\LaminasRbac` as a model of Role-Based Access Control (RBAC).

## How it works

In Dotkernel API each authenticatable entity (admin/user) comes with their `roles` table where you can define roles for each entity.
RBAC comes in to ensure that each entity has the appropriate role and permission to access a resource.

The authorization happens through the `Api\App\Middleware\AuthorizationMiddleware` middleware.

## Configuration

Dotkernel API makes use of `mezzio-authorization-rbac` and includes the full configuration.

The configuration file for the role and permission definitions is `config/autoload/authorization.global.php`.

```php
'mezzio-authorization-rbac' => [
    'roles'       => [
        AdminRole::ROLE_SUPERUSER => [],
        AdminRole::ROLE_ADMIN     => [
            AdminRole::ROLE_SUPERUSER,
        ],
        UserRole::ROLE_GUEST      => [
            UserRole::ROLE_USER,
        ],
    ],
    'permissions' => [
        AdminRole::ROLE_SUPERUSER => [],
        AdminRole::ROLE_ADMIN     => [
            'other.routes'
            'admin.list',
            'home'
        ],
        UserRole::ROLE_USER       => [
            'other.routes',
            'user.my-account.update',
            'user.my-account.view',
        ],
        UserRole::ROLE_GUEST      => [
            'other.routes',
            'security.refresh-token',
            'error.report',
            'home',
        ],
    ],
],
```

> See [mezzio-authorization-rbac](https://docs.mezzio.dev/mezzio-authorization-rbac/v1/basic-usage/)
> for more information.

## Usage

Based on the configuration file above, we have two admin roles (`superuser`, `admin`) and two user roles (`user`, `guest`).

Roles inherit the permissions from their parents:

- `superuser` has no parent
- `admin` has `superuser` as a parent which means `superuser` also has `admin` permissions
- `user` has no parent
- `guest` has `user` as a parent which means `user` also has `guest` permissions

For each role we defined an array of permissions.
A permission in Dotkernel API is basically a route name.

As you can see, the `superuser` does not have its own permissions, because it gains all the permissions from `admin`, no need to define explicit permissions.

The `user` role, gains all the permission from `guest` so no need to define that `user` can access `home` route, but `guest` cannot access user-specific routes.

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

**Q: How does role inheritance work here?**

A: A role listed inside another role's entry is its parent's beneficiary: because `admin` lists `superuser`, `superuser` receives everything granted to `admin`.
That is why `superuser` needs no explicit permissions of its own.

**Q: Where are roles stored?**

A: Each authenticatable entity — admin or user — has its own `roles` table where its roles are defined.

**Q: Which middleware enforces this?**

A: `Api\App\Middleware\AuthorizationMiddleware`.
See [Middleware flow](../flow/middleware-flow.md).

**Q: Can I use ACL instead of RBAC?**

A: The ACL adapter ships with the project, but RBAC is what Dotkernel API is configured for; switching means replacing the authorization configuration.
