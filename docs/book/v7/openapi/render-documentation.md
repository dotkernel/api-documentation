# Rendering the documentation file

## Summary

A generated OpenAPI file is only data; to browse and try your endpoints you need a renderer.
This page shows how to serve the file from the `public` directory through either Swagger UI or Redoc, using a small HTML page that points at your `openapi.yaml` or `openapi.json`.

## Details

At this step, you only have a static documentation file.
You will need an interface that can render it so that you will be able to interact with your Dotkernel API.

To do this, we recommend using either of:

- [swagger-api/swagger-ui](https://github.com/swagger-api/swagger-ui)
- [Redocly/redoc](https://github.com/Redocly/redoc)

## Using Swagger UI

Navigate to the `public` directory of your instance of Dotkernel API and create an HTML (you can call it `swagger.html`, the name is up to you) and place the following HTML content in it:

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

Using your browser, open a new tab and type in the URL of your instance of Dotkernel API and append `/swagger.html` to it.
You should see the Redoc interface with your documentation file loaded in it.
From here, you can inspect each endpoint, see its URL, check if it needs authentication, the request payload (if any) and the possible response(s).

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

Make sure that you replace `PATH_TO_YOUR_OPENAPI_FILE` with the relative path to your documentation file (openapi.json/openapi.yaml).
The line should look similar to this:

```js
Redoc.init('./openapi.yaml', {}, document.getElementById('redoc-container'));
```

Using your browser, open a new tab and type in the URL of your instance of Dotkernel API and append `/redoc.html` to it.
You should see the Redoc interface with your documentation file loaded in it.
From here, you can inspect each endpoint, see its URL, check if it needs authentication, the request payload (if any) and the possible response(s).

## FAQ

**Q: Should I choose Swagger UI or Redoc?**

A: Swagger UI offers an interactive console for sending requests, while Redoc renders a cleaner read-only reference.
Both consume the same OpenAPI file, so you can set up either or both.

**Q: Why must the HTML file live in `public`?**

A: Only `public` is served directly by the web server; files elsewhere are routed through `index.php`.
The page and the OpenAPI file both need to be reachable by the browser.

**Q: Does the filename matter?**

A: No. `swagger.html` and `redoc.html` are suggestions — the URL you open just has to match whatever you named the file.

**Q: My page loads but shows no endpoints. What is wrong?**

A: `PATH_TO_YOUR_OPENAPI_FILE` was most likely not replaced, or the relative path does not resolve.
Set it to something like `./openapi.yaml`, matching where you generated the file.
See [Generate documentation](generate-documentation.md).

**Q: Can the renderer read a JSON file instead of YAML?**

A: Yes.
Both renderers accept either format; point the URL at `openapi.json` if that is what you generated.

**Q: Should I expose this in production?**

A: It is not recommended.
Enabling the documentation publicly widens your attack surface.
See [Basic security](../security/basic-security.md).

**Q: Do these pages load resources from the internet?**

A: Yes — the examples pull Swagger UI and Redoc from a CDN.
Host the assets yourself if your environment has no outbound access.
