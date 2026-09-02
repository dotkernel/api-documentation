# Upgrades

## Summary

Dotkernel API has no automatic upgrade path.
You upgrade by applying the changes from each release to your own project by hand, using the project `CHANGELOG.md` to track which version your copy corresponds to.

## Details

Dotkernel API does not provide an automatic upgrade path.
Instead, the recommended procedure is to manually implement each modification listed in [releases](https://github.com/dotkernel/api/releases).
Additionally, release info can also be accessed as an [RSS](https://github.com/dotkernel/api/releases.atom) feed.

## Upgrade procedure

Once you clone Dotkernel API, you will find a [CHANGELOG.md](https://github.com/dotkernel/api/blob/5.0/CHANGELOG.md) file in the root of the project.
This file contains a list of already implemented features in reverse chronological order.
You can use this file to track the version of your copy of Dotkernel API.

For each new release you need to implement the modifications from its pull requests in your project.
It is recommended to copy the release info into your project's CHANGELOG.md file.
This allows you to track your API's version and keep your project up to date with future releases.

## Version to version upgrading

Starting from [version 5.3](UPGRADE-6.0.md) the upgrading procedure is detailed version to version.

## FAQ

**Q: Why is there no automatic upgrade path?**

A: Dotkernel API is a project skeleton you own and modify, not a dependency you bump.
Because your code lives alongside the skeleton's, only you can decide how each upstream change applies to it.

**Q: How do I tell which version my project is based on?**

A: Compare the entries in your project's `CHANGELOG.md` with the upstream one.
The last upstream entry you have applied is your effective version.

**Q: Do I have to apply every release in order?**

A: Applying them in order is strongly recommended, since later changes often build on earlier ones.
Skipping releases means reconciling several sets of changes at once.

**Q: How do I stay informed about new releases?**

A: Watch the [releases page](https://github.com/dotkernel/api/releases) or subscribe to the [RSS feed](https://github.com/dotkernel/api/releases.atom).

**Q: Where do I find instructions for a specific major version jump?**

A: Version-to-version pages exist from 5.3 onward, for example [Upgrading from 5.x to 6.0](UPGRADE-6.0.md) and [Upgrading from 6.x to 7.0](UPGRADE-7.0.md).
