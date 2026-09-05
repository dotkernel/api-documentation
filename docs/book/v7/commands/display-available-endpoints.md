# Displaying Dotkernel API endpoints using dot-cli

## Summary

The `route:list` CLI command inspects the application's routes at runtime and prints a numbered table of every endpoint's request method, route name and path.
Results can be filtered by name, path or method.

## Usage

Run the following command in your application’s root directory:

```shell
php ./bin/cli.php route:list
```

The command runs through all routes and extracts endpoint information in realtime.
Rows are sorted by path, then by request method, and UUID route parameters are shown as `{id}` rather than the full regular expression they are declared with.
The count in the table header reflects method/path pairs, so a path answering three methods contributes three rows.

The output should be similar to the following:

```text
+------+----------------+-------------------- 38 Routes ------+-------------------------------------+
|    # | Request method | Route name                          | Route path                          |
+------+----------------+-------------------------------------+-------------------------------------+
|    1 | GET            | app::view-index                     | /                                   |
|    2 | GET            | admin::list-admin                   | /admin                              |
|    3 | POST           | admin::create-admin                 | /admin                              |
|    4 | GET            | admin::view-account                 | /admin/account                      |
|    5 | PATCH          | admin::update-account               | /admin/account                      |
|    6 | GET            | admin::list-role                    | /admin/role                         |
|    7 | GET            | admin::view-role                    | /admin/role/{id}                    |
|    8 | DELETE         | admin::delete-admin                 | /admin/{id}                         |
|    9 | GET            | admin::view-admin                   | /admin/{id}                         |
|   10 | PATCH          | admin::update-admin                 | /admin/{id}                         |
|   11 | POST           | app::create-error-report            | /error-report                       |
|   12 | POST           | security::generate-token            | /security/generate-token            |
|   13 | POST           | security::refresh-token             | /security/refresh-token             |
|   14 | GET            | user::list-user                     | /user                               |
|   15 | POST           | user::create-user                   | /user                               |
|   16 | DELETE         | user::delete-account                | /user/account                       |
|   17 | GET            | user::view-account                  | /user/account                       |
|   18 | PATCH          | user::update-account                | /user/account                       |
|   19 | POST           | user::create-account                | /user/account                       |
|   20 | POST           | user::request-activate-account      | /user/account/activate              |
|   21 | PATCH          | user::activate-account              | /user/account/activate/{hash}       |
|   22 | DELETE         | user::delete-account-avatar         | /user/account/avatar                |
|   23 | GET            | user::view-account-avatar           | /user/account/avatar                |
|   24 | POST           | user::create-account-avatar         | /user/account/avatar                |
|   25 | POST           | user::recover-account               | /user/account/recover               |
|   26 | POST           | user::create-account-reset-password | /user/account/reset-password        |
|   27 | GET            | user::check-account-reset-password  | /user/account/reset-password/{hash} |
|   28 | PATCH          | user::update-account-reset-password | /user/account/reset-password/{hash} |
|   29 | GET            | user::list-role                     | /user/role                          |
|   30 | GET            | user::view-role                     | /user/role/{id}                     |
|   31 | DELETE         | user::delete-user                   | /user/{id}                          |
|   32 | GET            | user::view-user                     | /user/{id}                          |
|   33 | PATCH          | user::update-user                   | /user/{id}                          |
|   34 | PATCH          | user::activate-user                 | /user/{id}/activate                 |
|   35 | DELETE         | user::delete-user-avatar            | /user/{id}/avatar                   |
|   36 | GET            | user::view-user-avatar              | /user/{id}/avatar                   |
|   37 | POST           | user::create-user-avatar            | /user/{id}/avatar                   |
|   38 | PATCH          | user::deactivate-user               | /user/{id}/deactivate               |
+------+----------------+-------------------------------------+-------------------------------------+
```

## Filtering results

The following filters can be applied when displaying the route list:

* Filter routes by name, using: `-i|--name[=NAME]`
* Filter routes by path, using: `-p|--path[=PATH]`
* Filter routes by method, using: `-m|--method[=METHOD]`

The filters are matched as case-sensitive substrings and can be combined.
For example, `php ./bin/cli.php route:list -i avatar` lists only the six avatar routes, and adding `-m GET` narrows that to two.

> Case matters.
> Route names and paths are lowercase and methods are uppercase, so `-i avatar` and `-m GET` match, while `-i Avatar` and `-m get` match nothing and print an empty table.

Get more help by running this command:

```shell
php ./bin/cli.php route:list --help
```

## FAQ

**Q: Is the output generated from a static file?**

A: No. The command walks the application's registered routes in realtime, so it always reflects the current configuration.

**Q: Which filters are available?**

A: `-i|--name`, `-p|--path` and `-m|--method`.
Each is a case-sensitive substring match, and they can be combined.
Use lowercase for names and paths and uppercase for methods — `-m get` matches nothing.

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
