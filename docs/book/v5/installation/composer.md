# Composer Installation of Packages

Composer is required to install DotKernel `frontend`. You can install Composer from the [official site](https://getcomposer.org/).

> First make sure that you have navigated your command prompt to the folder where you copied the files in the previous step.

## Install dependencies

Run this command in the command prompt.

> Use the **CLI** in order to ensure interactivity for proper configuration.

```shell
composer install
```

You should see this text below, along with a long list of packages to be installed instead of the `[...]`.
In this example there are 164 packages, though the number can change in future updates.
You will find the packages in the `vendor` folder.

```shell
No composer.lock file present. Updating dependencies to latest instead of installing from lock file. See https://getcomposer.org/install for more information.
Loading composer repositories with package information
Updating dependencies
Lock file operations: 164 installs, 0 updates, 0 removals
[...]
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 164 installs, 0 updates, 0 removals
[...]
```

The setup script may prompt for some configuration settings, for example the lines below.
If you don't see them, you can skip to the next section.

```shell
Please select which config file you wish to inject 'Laminas\Diactoros\ConfigProvider' into:
  [0] Do not inject
  [1] config/config.php
  Make your selection (default is 1):
```

Type `0` to select `[0] Do not inject` because DotKernel includes its own ConfigProvider which already contains the prompted configurations.

> If you choose `[1] config/config.php`, an extra `ConfigProvider` will be injected.
> This is not required for the default installation, so make sure to select `[0] Do not inject`.

The next question is:

`Remember this option for other packages of the same type? (y/N)`

Type `y` here, and hit `enter` to complete this stage.

## Development mode

If you're installing the project for development, make sure you have development mode enabled, by running:

```shell
composer development-enable
```

You can disable development mode by running:

```shell
composer development-disable
```

You can check if you have development mode enabled by running:

```shell
composer development-status
```
