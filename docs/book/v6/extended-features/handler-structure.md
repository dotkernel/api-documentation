# The new handler structure

The new Dotkernel 6.0 version contains some new architectural changes compared to it's older version that used controllers.
Current changes are in the form of:
* Handlers (replacing controllers)
* Mapping
* Patterns

## What is a handler?

In DotKernel 6.0, a "handler" is the piece of code that reacts when a user makes a specific request (like visiting a webpage or submitting a form).
It's basically the "controller" that decides what happens next.

## What is the Map?

Instead of manually linking every URL to a controller, DotKernel 6.0 introduces a "map and pattern" system.
This system can automatically find the correct handler based on the request's path.

```php
$routeCollector->group('/user/' . $uuid)
    ->delete('', DeleteUserResourceHandler::class, 'user::delete-user')
    ->get('', GetUserResourceHandler::class, 'user::view-user')
    ->patch('', PatchUserResourceHandler::class, 'user::update-user');
```

## What is a Pattern?

A pattern is an automatic way of guessing the handler from the URL structure.

Example:

* User accesses /about-us
* First, DotKernel looks in the map: is there a key 'about-us'?
* If not found, it tries a pattern:
  * Transform about-us into AboutUsHandler
  * Looks for \App\Handler\AboutUsHandler
