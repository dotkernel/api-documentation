# OAuth2 Security

Dotkernel API uses the [mezzio/mezzio-authentication-oauth2](https://github.com/mezzio/mezzio-authentication-oauth2) component to provide the OAuth2 authentication service.
As a security stating point, when developing an application using this project make sure you go over the following steps.

## Default OAuth Clients

The project ships with the default OAuth clients `admin` and `frontend` with passwords equal to their names, as described in the [Authentication](https://docs.dotkernel.org/api-documentation/v6/core-features/authentication/) guide.

These clients **must not** remain unchanged in your production environment, as they are a security risk -
ensure you deleted them or updated the passwords.

## OAuth Token Lifetime and Refresh Hygiene

The configuration for OAuth2 tokens can be edited in `config/autoload/local.php` under the `authentication` key.

By default, the lifetimes of the `access` and `refresh` tokens are set to one day and one month respectively.
Make sure to adjust their values in accordance to your application's needs, with lower values being generally safer.

> Read more about the available [configuration options](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration).

## Autogeneration of Cryptographic Keys

Dotkernel API makes use of the `./vendor/bin/generate-oauth2-keys` command from `mezzio-authentication-oauth2` to automatically regenerate the
public/private key pair used to verify the transmitted JWTs.
This process is done after each `composer update` (or `composer install` with no lock file), as specified in `composer.json` under the `scripts.post-update-cmd` key.

While hidden to the VCS by default, keep in mind not to commit any local keys.

> Autogeneration of keys can be disabled by simply removing the `php ./vendor/bin/generate-oauth2-keys` command from the mentioned key.
> While not related to Dotkernel API itself, do ensure that the directory containing the keys is properly secured.
