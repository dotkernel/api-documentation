# UPGRADE FROM 5.2 TO 5.3

-------------------------

Dotkernel API 5.3 is a minor release. As such, no significant backward compatibility breaks are expected,
with minor backward compatibility breaks being prefixed in this document with `[BC BREAK]`.

This document only covers upgrading from version 5.2.

## Table of Contents

-------------------------

* [Update Phpstan memory limit](#update-phpstan-memory-limit)
* [Update anonymization](#update-anonymization)
* [Update User status and remove isDeleted properties](#update-user-status-and-remove-isdeleted-properties)
* [Update dotkernel/dot-mail to version 5.0](#update-dotkerneldot-mail-to-version-50)
* [Add post install script](#add-post-install-script)
* [Remove post-create-project-cmd](#remove-post-create-project-cmd)
* [Update security.txt](#update-securitytxt)
* [Update coding standards](#update-coding-standards)
* [Fix codecov.yml](#fix-codecovyml)
* [Update Qodana configuration](#update-qodana-configuration)
* [Remove laminas/laminas-http](#remove-laminaslaminas-http)

### Update Phpstan memory limit

* Add the `--memory-limit 1G` option to the `static-analysis` script found in `composer.json`
  > With the default `memory_limit=128M` on our WSL containers, PHPStan runs out of memory
  >
  > Note that you can set the memory limit to a value of your choosing, with a recommended minimum of 256M

### Update anonymization

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

* [BC Break] Remove the `isDeleted` property from the `User` class, alongside all usages, as seen in the [pull request](https://github.com/dotkernel/api/pull/359/files)
* Add a new "deleted" case to `UserStatusEnum`, which is to be used instead of the previous `isDeleted` property
* Update the database and it's migrations to reflect the new structure
  > The use of "isDeleted" was redundant in the default application, and as such was removed
  >
  > All default methods are updated, but any custom functionality using "isDeleted" will require refactoring

### Update `dotkernel/dot-mail` to version 5.0

* Bump `dotkernel/dot-mail` to "^5.0" in `composer.json`
* As the mail configuration file is now directly copied from the vendor via [script](#add-post-install-script), remove the existing `config/autoload/mail.global.php[.dist]` file(s)
* Update the content for each of these configuration files to reflect the new structure from [dotkernel/dot-mail](https://github.com/dotkernel/dot-mail/blob/5.0/config/mail.global.php.dist)
* Remove `Laminas\Mail\ConfigProvider::class` from `config/config.php`
  > The list of changes can be seen in the [pull request](https://github.com/dotkernel/api/pull/368/files)
  >
  > You can read more about the reasons for this change on the [Dotkernel blog](https://www.dotkernel.com/dotkernel/replacing-laminas-mail-with-symfony-mailer-in-dot-mail/).

### Remove `post-create-project-cmd`

* Remove the `post-create-project-cmd` key found under `scripts` in `composer.json`

```json
"post-create-project-cmd": [
    "@development-enable"
],
```

### Add post install script

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

### Update security.txt

* Add the `Preferred-Languages` key to `public/.well-known/security.txt`
  > You may include more than one language as comma separated language tags

### Update coding standards

* Bump `laminas/laminas-coding-standard` to `^3.0` in `composer.json`
* Add the following to `phpcs.xml` to prevent issues with the fully qualified names from `config/config.php`:

```xml
<rule ref="LaminasCodingStandard">
    <!-- Exclude rule -->
    <exclude name="SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName" />
</rule>
```

### Fix codecov.yml

* Change `COMPOSER_DEV_MODE=1` to the correct syntax `COMPOSER_DEV_MODE: 1`

### Update Qodana configuration

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

* Remove `laminas/laminas-http` from `composer.json`
* Replace all uses of `Laminas\Http\Response` with `Fig\Http\Message\StatusCodeInterface` in `AuthorizationMiddlewareTest.php` and `ContentNegotiationMiddlewareTest.php`
