# OAuth2 Security

## Summary

The security steps to take before an OAuth2-protected Dotkernel API reaches production: remove or re-password the default `admin` and `frontend` OAuth clients, tune access and refresh token lifetimes, and understand how the JWT signing key pair is regenerated and where it must be kept.

## Details

Dotkernel API uses the [mezzio/mezzio-authentication-oauth2](https://github.com/mezzio/mezzio-authentication-oauth2) component to provide the OAuth2 authentication service.
As a security stating point, when developing an application using this project, make sure you go over the following steps.

## Default OAuth Clients

The project ships with the default OAuth clients `admin` and `frontend` with passwords equal to their names, as described in the [Authentication](https://docs.dotkernel.org/api-documentation/v7/core-features/authentication/) guide.

These clients **must not** remain unchanged in your production environment, as they are a security risk; ensure you deleted them or updated the passwords.

## OAuth Token Lifetime and Refresh Hygiene

The configuration for OAuth2 tokens can be edited in `config/autoload/local.php` under the `authentication` key.

By default, the lifetimes of the `access` and `refresh` tokens are set to one day and one month respectively.
Make sure to adjust their values in accordance with your application's needs, with lower values being generally safer.

> If your application requires it, you can revoke user OAuth tokens before their expiration by making use of the `revokeTokens` method of `UserService`.
>
> Read more about the available [configuration options](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration).

## Autogeneration of Cryptographic Keys

Dotkernel API makes use of the `./vendor/bin/generate-oauth2-keys` command from `mezzio-authentication-oauth2` to automatically regenerate the
public/private key pair used to verify the transmitted JWTs.
This process is done after each `composer update` (or `composer install` with no lock file), as specified in `composer.json` under the `scripts.post-update-cmd` key.

While hidden to the VCS by default, keep in mind not to commit any local keys.

> Autogeneration of keys can be disabled by simply removing the `php ./vendor/bin/generate-oauth2-keys` command from the mentioned key.
>
> While not related to Dotkernel API itself, do ensure that the directory containing the keys is properly secured.

## FAQ

**Q: What is the single most important step before going live?**

A: Deleting or re-passwording the default `admin` and `frontend` OAuth clients.
They ship with passwords equal to their names, so leaving them in place hands anyone a valid client.

**Q: Where do I change token lifetimes?**

A: Under the `authentication` key in `config/autoload/local.php`.
Defaults are one day for access tokens and one month for refresh tokens; shorter values are generally safer.

**Q: Can I invalidate a user's tokens before they expire?**

A: Yes, via the `revokeTokens` method of `UserService`.

**Q: When are the OAuth2 keys regenerated?**

A: After every `composer update`, and after `composer install` when there is no lock file, through the `php ./vendor/bin/generate-oauth2-keys` script in `composer.json`.

**Q: How do I stop the keys from being regenerated?**

A: Remove `php ./vendor/bin/generate-oauth2-keys` from the `scripts.post-update-cmd` key in `composer.json`.
This matters on servers where regenerating keys would invalidate tokens already issued.

**Q: Should the key pair be committed?**

A: No. The keys are excluded from version control by default, and the directory holding them must be secured at the filesystem level.

**Q: Where are the OAuth2 flows themselves documented?**

A: In [Authentication](../core-features/authentication.md) and the [Token authentication](../tutorials/token-authentication.md) tutorial.
