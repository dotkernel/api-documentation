# Rendering the documentation file

At this step, you only have a static documentation file. You will need an interface that can render it so that you will
be able to interact with your Dotkernel API.

In order to do this, we recommend using either of:

- [swagger-api/swagger-ui](https://github.com/swagger-api/swagger-ui)
- [Redocly/redoc](https://github.com/Redocly/redoc)

## Using Swagger UI

Navigate to the `public` directory of your instance of Dotkernel API and create an HTML (you can call it `swagger.html`,
the name is up to you) and place the following HTML content in it:

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Dotkernel API Documentation" />
    <title>Dotkernel API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
  </head>
  <body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin></script>
    <script>
      window.onload = () => {
        window.ui = SwaggerUIBundle({url: 'PATH_TO_YOUR_OPENAPI_FILE', dom_id: '#swagger-ui'});
      };
    </script>
  </body>
</html>
```

Make sure that you replace `PATH_TO_YOUR_OPENAPI_FILE` with the relative path to your documentation file
(openapi.json/openapi.yaml). The line should look similar to this:

```js
window.ui = SwaggerUIBundle({url: './openapi.yaml', dom_id: '#swagger-ui'});
```

Using your browser, open a new tab and type in the URL of your instance of Dotkernel API and append `/swagger.html` to
it. You should see the Redoc interface with your documentation file loaded in it. From here, you can inspect each
endpoint, see it's URL, check if it needs authentication, the request payload (if any) and the possible response(s).

## Using Redoc

Navigate to the `public` directory of your instance of Dotkernel API and create an HTML (you can call it `redoc.html`,
the name is up to you) and place the following HTML content in it:

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Dotkernel API Documentation" />
    <title>Dotkernel API Documentation</title>
    <script src="https://cdn.jsdelivr.net/npm/redoc@latest/bundles/redoc.standalone.js"></script>
  </head>
  <body>
    <div id="redoc-container"></div>
    <script>
      Redoc.init('PATH_TO_YOUR_OPENAPI_FILE', {}, document.getElementById('redoc-container'));
    </script>
  </body>
</html>
```

Make sure that you replace `PATH_TO_YOUR_OPENAPI_FILE` with the relative path to your documentation file
(openapi.json/openapi.yaml). The line should look similar to this:

```js
Redoc.init('./openapi.yaml', {}, document.getElementById('redoc-container'));
```

Using your browser, open a new tab and type in the URL of your instance of Dotkernel API and append `/redoc.html` to it.
You should see the Redoc interface with your documentation file loaded in it. From here, you can inspect each endpoint,
see it's URL, check if it needs authentication, the request payload (if any) and the possible response(s).
