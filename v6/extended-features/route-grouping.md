# Route grouping

In Dotkernel 6.0 with the help of the new [dot-router](https://docs.dotkernel.org/dot-router/v1/overview/) package, we have managed to implement a nicer way of creating routes.
A lot of the times developers need to create sets of routes that have a similar format. As an example:

```php
$app->post('/product/create', CreateProductHandler::class, 'product:create');
$app->delete('/product/delete/{id}', DeleteProductHandler::class, 'product:delete');
$app->patch('/product/update/{id}', UpdateProductHandler::class, 'product:update');
$app->get('/product/view/{id}', GetProductHandler::class, 'product:view');
```

Along with the features from `mezzio/mezzio-fastroute`, the new `dot-router` package provides the ability to create route groups which are collections of routes that have the same base string for the path.

Here we have an example from `src/User/src/RoutesDelegator.php` with the new grouping method:

```php
$routeCollector->group('/user/' . $uuid)
    ->delete('', DeleteUserResourceHandler::class, 'user::delete-user')
    ->get('', GetUserResourceHandler::class, 'user::view-user')
    ->patch('', PatchUserResourceHandler::class, 'user::update-user');
```

The advantages of this new implementation:

- **DRY**- no need for repeating common route parts
- **encapsulation** - similar routes are grouped in a single block of code (vs each route a separate statement)
- **easy path refactoring** - modify all routes at once by changing only the prefix
- **easy copying/moving** - copying/moving an entire group makes sure that you don't accidentally omit a route
