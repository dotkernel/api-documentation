# Transition approach  [WIP]

Dotkernel API is not a one-to-one replacement of api-tools (former Apigility), but is only a potential solution to migrate to.

Functionalities, components and architecture are different.

See the [Comparison between Dotkernel APi and api-tools](https://docs.dotkernel.org/api-documentation/v4/transition-from-api-tools/api-tools-vs-dotkernel-api/).

## Business cases

There are at least two approaches for this transition:

- Clone 1:1 and recreate all endpoints and entities
- Build a new version of the current API using Dotkernel API and keep it running as separate platforms until the sunset of the current version of api-tools
