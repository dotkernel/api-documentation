# Composer Installation of Packages

Composer is required to install Dotkernel `api`. You can install Composer from the [official site](https://getcomposer.org/).

> First, make sure that you have navigated your command prompt to the folder where you copied the files in the previous step.

## Install dependencies

Run this command in the command prompt.

> Use the **CLI** to ensure interactivity for proper configuration.

```shell
composer install
```

The automatic setup script performs these tasks:

- Installs the packages listed in the `composer.json` file and their dependencies into the `vendor` folder.
- Creates the `composer.lock` file that locks all dependencies to exact versions (you can still run `composer update` to replace them with newer versions, if available).
- Configures PHP CodeSniffer, a utility to detect code style errors in PHP code.
- Generate and save the OAuth2 keys in the `data/oauth` folder.
- Creates the initial `config/autoload` configuration files:
    - config/autoload/local.php
    - config/autoload/local.test.php
    - config/autoload/mail.global.php

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
