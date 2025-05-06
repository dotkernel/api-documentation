# UPGRADE FROM 5.* TO 6.0 (WORK IN PROGRESS)

-------------------------

Dotkernel API 5.3 is a minor release. As such, no significant backward compatibility breaks are expected,
with minor backward compatibility breaks being prefixed in this document with `[BC BREAK]`.
This document only covers upgrading from version 5.2.

## Table of Contents

-------------------------

* [Update PHPStan memory limit](#update-phpstan-memory-limit)
* [Update anonymization](#update-anonymization)
* [Update User status and remove isDeleted properties](#update-user-status-and-remove-isdeleted-properties)
* [Update dotkernel/dot-mail to version 5.0](#update-dotkerneldot-mail-to-version-50)
* [Add post install script](#add-post-install-script)
* [Remove post-create-project-cmd](#remove-post-create-project-cmd)
* [Ignore development files on production env](#ignore-development-files-on-production-env)
* [Update security.txt](#update-securitytxt)
* [Update coding standards](#update-coding-standards)
* [Update Qodana configuration](#update-qodana-configuration)
* [Remove laminas/laminas-http](#remove-laminaslaminas-http)

### Update PHPStan memory limit

Following PHPStan's introduction in version 5.2 for the reasons described on the [Dotkernel blog](https://www.dotkernel.com/php-development/static-analysis-replacing-psalm-with-phpstan/) a minor issue has cropped up:
 with the default `memory_limit=128M` on our WSL containers, PHPStan runs out of memory

* Add the `--memory-limit 1G` option to the `static-analysis` script found in `composer.json`
  > Note that you can set the memory limit to a value of your choosing, with a recommended minimum of 256M

### Update anonymization

By default, Dotkernel API uses "soft delete" for its `User` entities in order to preserve the database entries.
Anonymization is used to make sure any sensitive information is scrubbed from the system, with the `User`'s `identity`, `email`, `firstName` and `lastName` properties being overwritten by a unique placeholder.
Version 5.3 is adding an optional suffix from a configuration file, from where it can be used anywhere in the application.

* Add the `userAnonymizeAppend` key to the returned array in `config/autoload/local.php`, as well as to the distributed`config/autoload/local.php.dist`

```php
'userAnonymizeAppend' => '',
```

* Update the `anonymizeUser` function in `src/User/src/Service/UserService.php` to use the new key

Before:

```php
$user->setIdentity($placeholder) //...
```

After:

```php
$user->setIdentity($placeholder . $this->config['userAnonymizeAppend']) //...
```

> Note that any custom functionality using the old format will require updates

### Update User status and remove isDeleted properties

Up to and including version 5.2, the `User` entity made use of the `UserStatusEnum` to mark the account status (`active` or `inactive`) and marked deleted accounts with the `isDeleted` property.
Starting from version 5.3 the `isDeleted` property has been removed because, by default, there is no use in having both it and the status property.
As such, a new `Deleted` case for `UserStatusEnum` is now used to mark a deleted account and remove the redundancy.

* [BC Break] Remove the `isDeleted` property from the `User` class, alongside all usages, as seen in the [pull request](https://github.com/dotkernel/api/pull/359/files)
* Add a new "deleted" case to `UserStatusEnum`, which is to be used instead of the previous `isDeleted` property
* Update the database and its migrations to reflect the new structure
  > The use of "isDeleted" was redundant in the default application, and as such was removed
  >
  > All default methods are updated, but any custom functionality using "isDeleted" will require refactoring

### Update `dotkernel/dot-mail` to version 5.0

Dotkernel API uses `dotkernel/dot-mail` to handle the mailing service, which in versions older than 5.0 was based on `laminas/laminas-mail`.
Due to the deprecation of `laminas/laminas-mail`, a decision was made to switch `dot-mail` to using `symfony/mailer` starting from version 5.0.
To make the API more future-proof, the upgrade to the new version of `dot-mail` was necessary.
The default usage of the mailer remains unchanged, with the only required updates being to configuration, as described below:

* Bump `dotkernel/dot-mail` to "^5.0" in `composer.json`
* As the mail configuration file is now directly copied from the vendor via [script](#add-post-install-script), remove the existing `config/autoload/mail.global.php[.dist]` file(s)
* Update the content for each of these configuration files to reflect the new structure from [dotkernel/dot-mail](https://github.com/dotkernel/dot-mail/blob/5.0/config/mail.global.php.dist)
* Remove `Laminas\Mail\ConfigProvider::class` from `config/config.php`
  > The list of changes can be seen in the [pull request](https://github.com/dotkernel/api/pull/368/files)
  >
  > You can read more about the reasons for this change on the [Dotkernel blog](https://www.dotkernel.com/dotkernel/replacing-laminas-mail-with-symfony-mailer-in-dot-mail/).

### Remove `post-create-project-cmd`

Installing the API via `composer create-project` is not recommended, and because of this the `post-create-project-cmd` has been removed.

* Remove the `post-create-project-cmd` key found under `scripts` in `composer.json`

```json
"post-create-project-cmd": [
    "@development-enable"
],
```

### Add post install script

To make installing the API less of a hassle, a new post installation script was added.
This script generates all the configuration files required by default, leaving the user to simply complete the relevant data.

> Note that the script will not overwrite existing configuration files, preserving any user data
>
> In case the structure of a configuration file needs updating (such as [mail.local.php](#update-dotkerneldot-mail-to-version-50) in this update), simply running the script *will not* make the changes

* Add `bin/composer-post-install-script.php` to automate the post installation copying of distributed configuration files
* Add the following under the `scripts` key in `composer.json`:

```json
"post-update-cmd": [
    "php bin/composer-post-install-script.php"
],
```

* Remove the following section from `.github/workflows/codecov.yml` and `.github/workflows/static-analysis.yml`

```yaml
- name: Setup project
  run: |
      mv config/autoload/local.php.dist config/autoload/local.php
      mv config/autoload/mail.global.php.dist config/autoload/mail.global.php
      mv config/autoload/local.test.php.dist config/autoload/local.test.php
```

> The command can be manually run via `php bin/composer-post-install-script.php`

### Ignore development files on production env

These tweaks were added to make sure development files remain untouched on production environments.

* Restrict codecov to development mode by changing the following section from `.github/workflows/codecov.yml`:

Before:

```yaml
- name: Install dependencies with composer
  run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader --ansi
```

After:

```yaml
- name: Install dependencies with composer
  env:
    COMPOSER_DEV_MODE: 1
  run: composer install --prefer-dist --no-interaction --no-progress --optimize-autoloader --ansi
```

* Edit `.laminas-ci/pre-run.sh` script by changing `echo "Running $COMMAND"` to `echo "Running pre-run $COMMAND"` and delete the following line:

```shell
cp config/autoload/mail.global.php.dist config/autoload/mail.global.php
```

### Update security.txt

Updated the `security.txt` file to define the preferred language of the security team.
It is recommended that the `Expires` tag is also updated if necessary.

* Add the `Preferred-Languages` key to `public/.well-known/security.txt`
  > You may include more than one language as comma separated language tags

### Update coding standards

Dotkernel API uses `laminas/laminas-coding-standard` as its baseline ruleset to ensure adherence to PSR-1 and PSR-12.
As this package had a major release, the minimum version the API uses was also bumped.

* Bump `laminas/laminas-coding-standard` to `^3.0` in `composer.json`
* Add the following to `phpcs.xml` to prevent issues with the fully qualified names from `config/config.php`:

```xml
<rule ref="LaminasCodingStandard">
    <!-- Exclude rule -->
    <exclude name="SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName" />
</rule>
```

### Update Qodana configuration

The Qodana code quality workflow has changed its default PHP version to 8.4, which is unsupported by Dotkernel API, resulting in errors.
The issue was fixed by restricting Qodana to the supported PHP versions.

* Update `.github/workflows/qodana_code_quality.yml`, specifying the supported PHP versions by adding the `strategy` key:

```yaml
strategy:
    matrix:
        php-versions: [ '8.2', '8.3' ]
```

* Update the `php-version` key to restrict Qodana to the newly added `php-versions`

Before:

```yaml
with:
    php-version: "${{ matrix.php }}"
```

After:

```yaml
with:
    php-version: ${{ matrix.php-versions }}
```

### Remove laminas/laminas-http

Prior to version 5.3, `laminas/laminas-http` was only used in 2 test files to assert if correct status codes were returned.
This dependency was removed, as the usage in tests was replaced with the existing `StatusCodeInterface`.

* Remove `laminas/laminas-http` from `composer.json`
* Replace all uses of `Laminas\Http\Response` with `Fig\Http\Message\StatusCodeInterface` in `AuthorizationMiddlewareTest.php` and `ContentNegotiationMiddlewareTest.php`
