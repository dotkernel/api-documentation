# Authorization

Authorization is the process by which a system take a validated identity and checks if that identity has access to a
given resource.

DotKernel's APIs implementation of the authorization uses `Mezzio\Authorization\Rbac\LaminasRbac` as a model of
Role-Based Access Control (RBAC)

## How it works

In DotKernel's API each authenticatable entity (admin and users) comes in with their roles table where you can define
roles for each entity. RBAC comes in to ensure that each entity has the appropriate role and permission to access a resource.

The authorization happens through the middleware in the `Api\App\Middleware\AuthorizationMiddleware`.

## Configuration

In DotKernel API make use of `mezzio-authorization-rbac` and upon installation all the configuration is already made
in order for the authorization to work.

The configuration where you define roles and permission is hold on `config/autoload/authorization.global.php`

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

> You can check [mezzio-authorization-rbac](https://docs.mezzio.dev/mezzio-authorization-rbac/v1/basic-usage/) for more
> in depth

## Usage

Based on the configuration file above, we have 2 admins roles (`superuser`, `admin`) and 2 users roles (`user`, `guest`).

A role can inherit the roles of their parent:

- `superuser` has no parent
- `admin` has `superuser` as a parent which means `superuser` will inherit `admin` permissions
- `user` has no parent
- `guest` has `user` as a parent which means `user` will inherit `guest` permissions

For each role we defined an array of permissions. A permission in DotKernel's is basically a route name.

As you can see, the `superuser` does not have any permission, because inherit all the permission from `admin`no
need to define permission for him again.

The `user` role, inherit all the permission from `guest` so no need to define that `user` can access `home` route, but
`guest` cannot access `user.my-account.view` route.
