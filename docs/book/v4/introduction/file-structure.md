# File structure

Dotkernel API follows the [PSR-4](https://www.php-fig.org/psr/psr-4/) standards.

It is a good practice to standardize the file structure of projects.

When using Dotkernel API, the following structure is installed by default:

![Dotkernel API File Structure!](https://docs.dotkernel.org/img/api/v4/file-structure-dk-api.png)

## Main directories

* `bin` - executable files from CLI
* `config` - various configuration files
* `data` - should contain project-related data (AVOID storing sensitive data on VCS)
* `documentation` - should contain project-related documentation
* `log` - storage of log files generated by dot-error-log library
* `public` - publicly visible files. The webserver need to have this folder as www-document root folder.
* `src` - should contain the source code files
* `test` - should contain the test files

## Special purpose folders

* `.github`  - containes workflow files
* `.laminas-ci` - contains laminas-ci workflow files

## `src` directory

This directory contains all source code related to the Module. It should contain following directories, if they’re not empty:

* Handler - Action classes (similar to Controllers but can only perform one action)
* Entity - For database entities
* Service - Service classes
* Collection - Database entities collections
* Repository - Entity repository folder

> The above example is just some of the directories a project may include, but these should give you an idea of how the structure should look like.

Other classes in the `src` directory may include `InputFilter`, `EventListener`, `Helper`, `Command`, `Factory` etc.

The `src` directory should also contain 2 files:

* `ConfigProvider.php` - Provides configuration data
* `RoutesDelegator.php` - Module main routes entry file

## `templates` directory

This directory contains the template files, used for example to help render e-mail templates.

> Dotkernel API uses twig as Templating Engine. All template files have the extension .html.twig

## `data` directory

This directory contains project-related data (such as cache, file uploads)

We recommend using the following directory structure:

* `data/cache` - location where caches are stored
* `data/oauth` - encryption, private and public keys needed for authentication.
* `data/doctrine` - fixtures and migrations
* `data/lock` - lock files generated by `dotkernel/dot-cli`  [See more](https://docs.dotkernel.org/dot-cli/v3/lock-files/)
