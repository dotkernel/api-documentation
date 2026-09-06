# Generating tokens in Dotkernel API

## Summary

`token:generate` is a multipurpose CLI command that issues the tokens different parts of the API require.
Currently it supports the `error-reporting` type, whose generated value is pasted into `config/autoload/error-handling.global.php`.

## Details

This is a multipurpose command that allows creating tokens required by different parts of the API.

## Usage

Go to your application's root directory.

Run the token generator command by executing the following command:

```shell
php ./bin/cli.php token:generate <type>
```

Where `<type>` is one of the following:

* [error-reporting](#generate-error-reporting-token)

If you need help using the command, execute the following command:

```shell
php ./bin/cli.php token:generate --help
```

### Generate error reporting token

You can generate an error reporting token by executing the following command:

```shell
php ./bin/cli.php token:generate error-reporting
```

The output should look similar to this:

```text
Error reporting token:

    0123456789abcdef0123456789abcdef01234567
```

Copy the generated token.

Open `config/autoload/error-handling.global.php` and paste the copied token as shown below:

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

Save and close `config/autoload/error-handling.global.php`.

> If your application is NOT in development mode, make sure you clear your config cache by executing:

```shell
php ./bin/clear-config-cache.php
```

## FAQ

**Q: Which token types can the command generate?**

A: `error-reporting`.
Run `php ./bin/cli.php token:generate --help` to see the current list.

**Q: What is the error reporting token for?**

A: It authorizes calls to the error reporting endpoint, so only clients holding the token can submit error reports.
See [Error reporting](../core-features/error-reporting.md).

**Q: Where do I put the generated token?**

A: In the `tokens` array under `ErrorReportServiceInterface::class` in `config/autoload/error-handling.global.php`.

**Q: Can I configure more than one token?**

A: Yes.
`tokens` is an array, so several valid tokens can coexist — useful when rotating a token without downtime.

**Q: The token has no effect after I saved the config. Why?**

A: Outside development mode the configuration is cached.
Clear it with `php ./bin/clear-config-cache.php`.

**Q: Does the command store the token for me?**

A: No.
It only prints the value; copying it into the configuration file is a manual step.
