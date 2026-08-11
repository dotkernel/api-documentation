# Composer Installation of Packages

## Summary

Running `composer install` pulls in the dependencies and also runs the project's setup scripts, which generate the OAuth2 keys and the initial `config/autoload` files.
This page covers the prompts you will be asked during installation and how to enable, disable and check development mode afterwards.

## Details

In this step you will:

- [Install dependencies](#install-dependencies-using-composer).
- [Enable development mode](#development-mode).

> Composer is required to install Dotkernel `api`.
> You can install Composer from the [official site](https://getcomposer.org/).

> Before you begin, make sure that you have navigated your command prompt to the folder where you copied the files in the previous step.

## Install Dependencies Using Composer

Run this command in the command prompt.

> Use the **CLI** to ensure interactivity for proper configuration.
> In some IDEs, Composer may not be able to prompt for configuration settings.

```shell
composer install
```

The automatic setup script performs these tasks:

- Installs the packages listed in the `composer.json` file and their dependencies into the `vendor` folder.
- Creates the `composer.lock` file that locks all dependencies to exact versions (you can still run `composer update` to replace them with newer versions, if available and installable without conflicts).
- Configures PHP CodeSniffer, a utility to detect code style errors in PHP code.
- Generate and save the OAuth2 keys in the `data/oauth` folder.
    - Performed by this script `./bin/generate-oauth2-keys.php`.
- Creates the initial `config/autoload` configuration files.
    - Performed by this script `./bin/composer-post-install-script.php`.
    - These files are created:
        - config/autoload/local.php
        - config/autoload/local.test.php
        - config/autoload/mail.global.php

> The post install commands are run automatically on every `composer install` and `composer update`.
> The scripts check if the files exist to prevent overwriting them.

You should see this text below, along with a long list of packages to be installed instead of the `[...]`.

> In this example there are 146 packages, though the number can change in future updates.

```shell
No composer.lock file present. Updating dependencies to latest instead of installing from lock file. See https://getcomposer.org/install for more information.
Loading composer repositories with package information
Updating dependencies
Lock file operations: 146 installs, 0 updates, 0 removals
[...]
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 146 installs, 0 updates, 0 removals
[...]
```

The setup script may prompt for some configuration settings, for example, the lines below.
If you don't see them, you can skip to the next section.

```shell
Please select which config file you wish to inject 'Laminas\Diactoros\ConfigProvider' into:
  [0] Do not inject
  [1] config/config.php
  Make your selection (default is 1):
```

Type `0` to select `[0] Do not inject`.

> If you choose `1`, an extra `ConfigProvider` will be injected, which may return an error for packages you add in the future.
> Choosing `0` prevents duplicate ConfigProvider registrations, as Dotkernel already includes its own.

The next question is:

`Remember this option for other packages of the same type? (y/N)`

Type `y` here, and hit `enter` to complete this stage.

## Development mode

Normally, a new project starts in development mode to prevent caching certain files in the `data/cache` folder.
Enable development mode by running:

```shell
composer development-enable
```

The confirmation message should read:

```shell
You are now in development mode.
```

If you ever need to disable the development mode, run:

```shell
composer development-disable
```

This command displays the development mode status:

```shell
composer development-status
```

You should see the message `Development mode is ENABLED` or `Development mode is DISABLED`.

## FAQ

**Q: Why should I run `composer install` from the CLI rather than an IDE?**

A: The setup script asks interactive questions, and some IDEs cannot display those prompts.
Answering them incorrectly — or not at all — leaves the project misconfigured.

**Q: What does the installation do besides downloading packages?**

A: It writes `composer.lock`, configures PHP CodeSniffer, generates the OAuth2 keys into `data/oauth`, and creates the initial `config/autoload` files.

**Q: Will re-running `composer install` overwrite my configuration?**

A: No. The post-install scripts run on every `composer install` and `composer update`, but they check whether each file already exists before writing it.

**Q: Composer asks where to inject `Laminas\Diactoros\ConfigProvider`. What do I answer?**

A: `0` — do not inject.
Dotkernel already registers its own ConfigProvider, and a duplicate registration can break packages you add later.

**Q: Should I answer `y` to remembering that choice?**

A: Yes.
It applies the same answer to other packages of the same type, so the rest of the installation runs without further prompts.

**Q: Why does the package count differ from the documentation?**

A: The number changes as dependencies are updated.
The example figure is only indicative.

**Q: What does development mode actually change?**

A: It stops certain files from being cached in `data/cache` and activates the development error handlers, so code and configuration changes take effect immediately.

**Q: How do I check or change development mode later?**

A: `composer development-status` reports the state, `composer development-enable` turns it on and `composer development-disable` turns it off.
Never leave it enabled in production.
See [Basic security](../security/basic-security.md).

**Q: What is the next installation step?**

A: Reviewing the generated configuration files.
See [Configuration files](configuration-files.md).
