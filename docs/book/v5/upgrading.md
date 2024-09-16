# Upgrades

Dotkernel API does not provide an automatic upgrade path.

Instead, the recommended procedure is to manually implement
each modification listed in [releases](https://github.com/dotkernel/api/releases).

Additionally, releases info can also be accessed as an [RSS](https://github.com/dotkernel/api/releases.atom) feed.

## Upgrade procedure

Once you clone Dotkernel API, you will find a [CHANGELOG.md](https://github.com/dotkernel/api/blob/5.0/CHANGELOG.md)
file in the root of the project.

This contains a list of already implemented features in reversed chronological order.

You can use this file to track the version your copy of Dotkernel API is at.

When there is a new release, you need to run through it and implement in your project the modifications from each pull
request.

Finally, copy the release info and paste it at the beginning of your project's CHANGELOG.md file.

This way you will be able to track your API's version info and keep your project up-to-date.
