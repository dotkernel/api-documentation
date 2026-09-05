# Error reporting endpoint

## Summary

The `/error-report` endpoint lets frontend applications post their own errors back to your API.
Access is controlled by an `Error-Reporting-Token` header plus a domain or IP whitelist, all configured in `config/autoload/error-handling.global.php`.
Accepted reports are appended to a log file that records the token, so you can tell which application sent each message.

## Details

The error reporting endpoint was designed to allow the **frontend developers** of your API to report any bugs they encounter securely that are fully under your control.
To prevent unauthorized usage, the endpoint is protected by a token in the request's header.

## Example case usage

- Frontend developed in Angular.
- Frontend developer will use try-catch in the code to send **frontend errors** back to the API.

## How to use it on the API side

Error reporting is done by sending a **POST** request to the `/error-report` endpoint, together with a **token** in the header.
In the sections below we will detail how to configure error reporting in your API and how the endpoint is used by the frontend developers.

### Generating a token and adding it to your API config

First, you need to generate a token for your request.
This is done by using the command:

```bash
php ./bin/cli.php token:generate error-reporting
```

The resulting token has this format `0123456789abcdef0123456789abcdef01234567`.

> This example is not a valid token, it just lets you know what to look for.

Copy the generated token in your `config/autoload/error-handling.global.php` file.
It should look similar to the example below.
Your API can have multiple tokens, if needed.

```php
return [
    ...
    ErrorReportServiceInterface::class => [
        ...
        'tokens' => [
            '0123456789abcdef0123456789abcdef01234567',
        ],
        ...
    ]
]
```

### Validation mechanism

Behind the scenes, the API validates your configuration and lets you know if any config items prevent the submission of the error report.
Below are the requirements for an application to be able to send error messages to Dotkernel API.

- **Server-side requirements** stored in `config/autoload/error-handling.global.php` (these can be set/overwritten in `config/autoload/local.php`):
    - All keys (`enabled`, `path`, `tokens`, `domain_whitelist` and `ip_whitelist`) must exist under `ErrorReportServiceInterface::class`.
    - The error reporting feature must be enabled by setting `ErrorReportServiceInterface::class` . `enabled` to `true`.
    - `ErrorReportServiceInterface::class` . `path` it must have a value; if the destination file does not exist, it will be created automatically.
    - `ErrorReportServiceInterface::class` . `tokens` must contain at least one token.
    - At least one of `ErrorReportServiceInterface::class` . `domain_whitelist`/`ip_whitelist` must have at least one value.

> In `src/App/src/Service/ErrorReportService.php`, the method `checkRequest()` tries to validate the request by checking matches for `domain_whitelist` with `isMatchingDomain()` and for `ip_whitelist` with `isMatchingIpAddress()`.
> If both return `false`, a `ForbiddenException` is thrown and the error message does not get stored.

- **Application-side requirements**:
    - Send the `Error-Reporting-Token` header with a valid token previously stored in `config/autoload/error-handling.global.php` in the `ErrorReportServiceInterface::class` . `tokens` array.
    - Send the `Origin` header set to the application's URL; this is the application that sends the error message.

> The tokens under `ErrorReportServiceInterface::class`->`tokens` do not expire.
> The log file stores the token value too, making it easy to identify which application sent the error message.

If your request passes all the checks, the message is saved in the log file specified in `ErrorReportServiceInterface::class`->`path`.

#### Tips and tricks

If there are multiple applications that report errors to your API, you can **assign a different error reporting token** for each.
The tokens support key-value pairs where:

- The **key** is an alias relevant to the assigned application that uses it.
- The **value** is the token itself.

Example:

```php
// ...
return [
    ...
    ErrorReportServiceInterface::class => [
        // ...
        'tokens' => [
            'frontend' => '0123456789abcdef0123456789abcdef01234567',
            'admin' => '9876543210abcdef0123456789abcdef7654321',
            // other tokens
        ],
    ],
];
```

The log file will have entries similar to the below:

> [2024-08-29 12:47:00] [0123456789abcdef0123456789abcdef01234567] Demo error message

The inclusion of the token helps you identify the source of the error message.
In our example, it's the application that uses the `0123456789abcdef0123456789abcdef01234567` token, which is assigned to the application `frontend`.

## How to use it on the Frontend side (Angular example)

The API developer sends a generated token to the frontend developer who will save it in their `environment.staging.ts` and/or `environment.prod.ts`.
From then on, it's the frontend developer's job to set up an error reporting function similar to the one below.

```javascript
postError(body: object): Promise<any> {
     return new Promise((resolve, reject) => {
      return this.http.post(API_ENDPOINT + 'error-report', body , {headers: new HttpHeaders({'Error-Reporting-Token': 'TOKEN', 'Origin': 'https://example.com'})})).subscribe({
        next: (response: any) => {
          resolve(response);
        },
        error: (e: HttpErrorResponse) => reject(e),
        complete: () => console.info('Error on sending error'),
      });
    });
  }
```

Whenever an error is found, the frontend will call `postError()` with a relevant description under `message`.

```javascript
apiService.postError({message: 'ERROR MESSAGE'})
```

## FAQ

**Q: Who is this endpoint for?**

A: Frontend and third-party applications that need to report their own errors back to an API you control, rather than to an external service.

**Q: How do I generate a token?**

A: Run `php ./bin/cli.php token:generate error-reporting` and copy the value into the `tokens` array under `ErrorReportServiceInterface::class`.
See [Generating tokens](../commands/generate-tokens.md).

**Q: Which headers must the reporting application send?**

A: `Error-Reporting-Token` with a configured token, and `Origin` set to the reporting application's URL.

**Q: Which configuration keys are required?**

A: All of `enabled`, `path`, `tokens`, `domain_whitelist` and `ip_whitelist` must exist under `ErrorReportServiceInterface::class`, with `enabled` set to `true`, `path` set to a value, at least one token, and at least one entry across the two whitelists.

**Q: Why is my report rejected with a `403`?**

A: Because `checkRequest()` in `ErrorReportService` matched neither the domain whitelist nor the IP whitelist, so a `ForbiddenException` was thrown and nothing was stored.

**Q: Can I override the settings per environment?**

A: Yes.
The keys live in `error-handling.global.php` but can be set or overridden in `config/autoload/local.php`, which is not committed.

**Q: How do I tell which application sent a report?**

A: The log entry includes the token used, and tokens can be defined as key-value pairs — an alias such as `frontend` mapped to the token — so each application gets its own identifiable token.

**Q: Do these tokens expire?**

A: No.
Because they are indefinite, rotate them manually from time to time.
See [Basic security](../security/basic-security.md).

**Q: Where do the reports end up?**

A: In the file named by `ErrorReportServiceInterface::class`, `path`.
If it does not exist, it is created automatically.

**Q: Does this endpoint need an auth token as well?**

A: No.
It is authorized solely by the error reporting token.
See [Using the documentation](../openapi/use-documentation.md).
