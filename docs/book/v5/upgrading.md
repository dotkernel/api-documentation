# Upgrades

Dotkernel API does not provide an automatic upgrade path.
Instead, the recommended procedure is to manually implement each modification listed in [releases](https://github.com/dotkernel/api/releases).
Additionally, release info can also be accessed as an [RSS](https://github.com/dotkernel/api/releases.atom) feed.

## Upgrade procedure

Once you clone Dotkernel API, you will find a [CHANGELOG.md](https://github.com/dotkernel/api/blob/5.0/CHANGELOG.md) file in the root of the project.
This file contains a list of already implemented features in reverse chronological order.
You can use this file to track the version of your copy of Dotkernel API.

For each new release you need implement the modifications from its pull requests in your project.
It is recommended to copy the release info into your project's CHANGELOG.md file.
This allows you to track your API's version and keep your project up-to-date with future releases.

Starting from [version 5.3](upgrading/UPGRADE-5.3.md) the upgrading procedure is detailed version to version.
