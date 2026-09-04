# OAuth2 Security

## Summary

The security steps to take before an OAuth2-protected Dotkernel API reaches production: remove or re-password the default `admin` and `frontend` OAuth clients, tune access and refresh token lifetimes, and understand how the JWT signing key pair is generated, why existing keys are preserved, and where they must be kept.

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

> If your application requires it, you can revoke a user's OAuth tokens before they expire.
> `UserService::revokeTokens()` is `private`, so it cannot be called from your own code; it runs as
> part of the public `UserService::deleteUser()`, which revokes the tokens and then anonymizes the
> account.
>
> To revoke tokens on their own, use the token repositories directly: fetch the user's tokens with
> `OAuthAccessTokenRepository::findAccessTokens($identity)`, then pass each token to
> `OAuthAccessTokenRepository::revokeAccessToken()` and
> `OAuthRefreshTokenRepository::revokeRefreshToken()`.
>
> Read more about the available [configuration options](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/intro/#configuration).

## Autogeneration of Cryptographic Keys

Dotkernel API runs its own `php ./bin/generate-oauth2-keys.php` script to create the public/private
key pair and the encryption key used to sign and verify the transmitted JWTs.
It is invoked after each `composer update` (or `composer install` with no lock file), as specified in
`composer.json` under the `scripts.post-update-cmd` key:

```json
"post-update-cmd": [
    "php ./bin/generate-oauth2-keys.php",
    "php ./bin/composer-post-install-script.php"
]
```

**Existing keys are never overwritten.** The script checks for `data/oauth/encryption.key`,
`data/oauth/private.key` and `data/oauth/public.key`; if all three are present it prints
`OAuth2 keys already exist. Skipping...` and stops.
Only when one is missing does it delegate to
`vendor/mezzio/mezzio-authentication-oauth2/bin/generate-oauth2-keys` to generate the set.

> This guard matters in production: regenerating the keys invalidates every access token already
> issued. Preserving them across updates was added in Dotkernel API 7.2.0
> ([issue #503](https://github.com/dotkernel/api/issues/503)).
> If you deliberately want to rotate the keys, delete the three files from `data/oauth` and run
> `composer update` — accepting that existing tokens stop working.

While hidden to the VCS by default, keep in mind not to commit any local keys.

> Key generation can be disabled by removing the `php ./bin/generate-oauth2-keys.php` entry from the mentioned key.
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

A: Yes, but not via `UserService::revokeTokens()` — that method is `private`.
It runs as part of the public `UserService::deleteUser()`, which also anonymizes the account.
To revoke tokens on their own, use the repositories: `OAuthAccessTokenRepository::findAccessTokens($identity)` to list them, then `revokeAccessToken()` and `OAuthRefreshTokenRepository::revokeRefreshToken()` for each.

**Q: When are the OAuth2 keys generated?**

A: `php ./bin/generate-oauth2-keys.php` runs after every `composer update`, and after `composer install` when there is no lock file, via `scripts.post-update-cmd` in `composer.json`.
It only generates keys that are missing: if all three files in `data/oauth` exist it reports `OAuth2 keys already exist. Skipping...` and leaves them alone, so updating dependencies does not invalidate issued tokens.

**Q: How do I stop the keys from being generated?**

A: Remove `php ./bin/generate-oauth2-keys.php` from the `scripts.post-update-cmd` key in `composer.json`.
Since 7.2.0 this is rarely necessary — the script already preserves existing keys, which is what protects issued tokens on a server.

**Q: How do I deliberately rotate the keys?**

A: Delete `encryption.key`, `private.key` and `public.key` from `data/oauth`, then run `composer update`.
The script regenerates the missing set.
Every access token issued under the old keys stops working, so plan for clients to re-authenticate.

**Q: Should the key pair be committed?**

A: No. The keys are excluded from version control by default, and the directory holding them must be secured at the filesystem level.

**Q: Where are the OAuth2 flows themselves documented?**

A: In [Authentication](../core-features/authentication.md) and the [Token authentication](../tutorials/token-authentication.md) tutorial.
