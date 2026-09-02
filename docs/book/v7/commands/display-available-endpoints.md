# Displaying Dotkernel API endpoints using dot-cli

## Summary

The `route:list` CLI command inspects the application's routes at runtime and prints every endpoint's request method, route name and path.
Results can be filtered by name, path or method.

## Usage

Run the following command in your application’s root directory:

```shell
php ./bin/cli.php route:list
```

The command runs through all routes and extracts endpoint information in realtime.
The output should be similar to the following:

```text
+-------------------- 37 Routes ------+-------------------------------------+
| Request method | Route name                          | Route path                          |
+----------------+-------------------------------------+-------------------------------------+
| GET            | app::view-index                     | /                                   |
| GET            | admin::list-admin                   | /admin                              |
| POST           | admin::create-admin                 | /admin                              |
| GET            | admin::view-account                 | /admin/account                      |
| PATCH          | admin::update-account               | /admin/account                      |
| GET            | admin::list-role                    | /admin/role                         |
| GET            | admin::view-role                    | /admin/role/{id}                    |
| DELETE         | admin::delete-admin                 | /admin/{id}                         |
| GET            | admin::view-admin                   | /admin/{id}                         |
| PATCH          | admin::update-admin                 | /admin/{id}                         |
| POST           | app::create-error-report            | /error-report                       |
| POST           | security::token                     | /security/token                     |
| GET            | user::list-user                     | /user                               |
| POST           | user::create-user                   | /user                               |
| DELETE         | user::delete-account                | /user/account                       |
| GET            | user::view-account                  | /user/account                       |
| PATCH          | user::update-account                | /user/account                       |
| POST           | user::create-account                | /user/account                       |
| POST           | user::request-activate-account      | /user/account/activate              |
| PATCH          | user::activate-account              | /user/account/activate/{hash}       |
| DELETE         | user::delete-account-avatar         | /user/account/avatar                |
| GET            | user::view-account-avatar           | /user/account/avatar                |
| POST           | user::create-account-avatar         | /user/account/avatar                |
| POST           | user::recover-account               | /user/account/recover               |
| POST           | user::create-account-reset-password | /user/account/reset-password        |
| GET            | user::check-account-reset-password  | /user/account/reset-password/{hash} |
| PATCH          | user::update-account-reset-password | /user/account/reset-password/{hash} |
| GET            | user::list-role                     | /user/role                          |
| GET            | user::view-role                     | /user/role/{id}                     |
| DELETE         | user::delete-user                   | /user/{id}                          |
| GET            | user::view-user                     | /user/{id}                          |
| PATCH          | user::update-user                   | /user/{id}                          |
| PATCH          | user::activate-user                 | /user/{id}/activate                 |
| DELETE         | user::delete-user-avatar            | /user/{id}/avatar                   |
| GET            | user::view-user-avatar              | /user/{id}/avatar                   |
| POST           | user::create-user-avatar            | /user/{id}/avatar                   |
| PATCH          | user::deactivate-user               | /user/{id}/deactivate               |
+------+----------------+-------------------------------------+-------------------------------------+

```

## Filtering results

The following filters can be applied when displaying the route list:

* Filter routes by name, using: `-i|--name[=NAME]`
* Filter routes by path, using: `-p|--path[=PATH]`
* Filter routes by method, using: `-m|--method[=METHOD]`

The filters are case-insensitive and can be combined.

Get more help by running this command:

```shell
php ./bin/cli.php route:list --help
```

## FAQ

**Q: Is the output generated from a static file?**

A: No. The command walks the application's registered routes in realtime, so it always reflects the current configuration.

**Q: Which filters are available?**

A: `-i|--name`, `-p|--path` and `-m|--method`.
They are case-insensitive and can be combined.

**Q: Why do route names matter beyond documentation?**

A: Because a permission in Dotkernel API is a route name, so this listing is also the list of permissions you can grant.
See [Authorization](../core-features/authorization.md).

**Q: My new route does not appear. What should I check?**

A: That its module's `RoutesDelegator` is registered and the route is declared there.
See [Route grouping](../extended-features/route-grouping.md).

**Q: How is this different from the OpenAPI documentation?**

A: `route:list` reports what the application actually routes; the OpenAPI file describes the documented contract.
Comparing the two is a quick way to spot undocumented endpoints.
See [OpenAPI documentation](../openapi/introduction.md).

**Q: Where do I see the full command help?**

A: Run `php ./bin/cli.php route:list --help`.
