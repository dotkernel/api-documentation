# CORS

## Summary

Browsers block cross-origin requests unless the API says otherwise.
This tutorial explains the mechanism, then walks through enabling it in Dotkernel API with `mezzio/mezzio-cors`: installing the package, registering its `ConfigProvider`, piping its middleware before routing, and configuring allowed origins, headers, cache duration and credentials in `config/autoload/cors.local.php`.

## What is CORS?

**Cross-Origin Resource Sharing** or _CORS_ is an HTTP header-based mechanism that allows a server to indicate any other origins (domain, scheme, or port) than its own from which a browser should permit loading of resources.

## Why do we need CORS?

When integrating an API, most developers have encountered the following error message:

> Access to fetch at _RESOURCE_URL_ from origin _ORIGIN_URL_ has been blocked by CORS policy: No ‘Access-Control-Allow-Origin’ header is present on the requested resource.

This happens because the API (_RESOURCE_URL_) is not configured to accept requests from the client (_ORIGIN_URL_).

## How to fix?

Dotkernel API fixes this issue using the [mezzio/mezzio-cors](https://github.com/mezzio/mezzio-cors) library.

### Step 1: Install the library

To install `mezzio/mezzio-cors`, run the following command:

```shell
composer require mezzio/mezzio-cors
```

### Step 2: Configure your API

#### Register ConfigProvider

Register `mezzio/mezzio-cors` in your application by adding its ConfigProvider to your application's config aggregator.
Open the file `config/config.php` and paste the below lines at the beginning of the array passed to `ConfigAggregator`:

```php
Laminas\Diactoros\ConfigProvider::class,
Mezzio\Cors\ConfigProvider::class,
```

Save and close the file.

#### Add middleware

Add `mezzio/mezzio-cors` middleware to your application's pipeline.
Open `config/pipeline.php` and paste the below line before the one with `RouteMiddleware::class`:

```php
$app->pipe(\Mezzio\Cors\Middleware\CorsMiddleware::class);
```

Save and close the file.

#### Create a config file

Create and open file `config/autoload/cors.local.php` and add the following code inside it:

```php
<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => [
            ConfigurationInterface::ANY_ORIGIN,
        ],
        'allowed_headers' => ['Accept', 'Content-Type', 'Authorization'],
        'allowed_max_age' => '600',
        'credentials_allowed' => true,
        'exposed_headers' => [],
    ],
];
```

This list explains the above configuration values:

- `allowed_origins`: an array of domains that are allowed to interact with the API (default `ConfigurationInterface::ANY_ORIGIN` which means that any domain can make requests to the API)
- `allowed_headers`: an array of allowed custom headers
- `allowed_max_age`: the maximum duration, since the preflight response may be cached by a client
- `credentials_allowed`: allows a request to pass cookies
- `exposed_headers`: an array of headers which are being exposed by the endpoint

Save and close the file.

> On the **production** environment, make sure you allow only specific origins by adding them to the `allowed_origins` array and removing the current value of `ConfigurationInterface::ANY_ORIGIN`.

For more info, see [mezzio/mezzio-cors documentation](https://docs.mezzio.dev/mezzio-cors/v1/middleware/#configuration).

## FAQ

**Q: Why do I get a "No 'Access-Control-Allow-Origin' header" error?**

A: The API is not configured to accept requests from the calling origin.
Add that origin to `allowed_origins`.

**Q: Where must the CORS middleware sit in the pipeline?**

A: Before `RouteMiddleware::class`, so preflight requests are answered without needing to match a route.

**Q: Which origins should production allow?**

A: Only the ones that genuinely consume the API.
Replace `ConfigurationInterface::ANY_ORIGIN` with an explicit list before deploying.

**Q: What does `credentials_allowed` control?**

A: Whether the browser may send cookies with cross-origin requests.
Enable it only if your clients rely on cookie-based state.

**Q: What is `allowed_max_age` for?**

A: It sets how long a client may cache the preflight response, in seconds.
A higher value means fewer preflight round trips.

**Q: When do I need to change `allowed_headers`?**

A: Whenever clients send a header not already listed — the defaults cover `Accept`, `Content-Type` and `Authorization`.

**Q: What is `exposed_headers` for?**

A: It lists response headers the browser should make readable to client-side code; by default only a small set of standard headers is exposed.

**Q: Do I need to install the package on a fresh Dotkernel API?**

A: No.
`mezzio/mezzio-cors` ships with the project and `cors.local.php` is created during installation — you only need to review its values.
See [Configuration files](../installation/configuration-files.md).
