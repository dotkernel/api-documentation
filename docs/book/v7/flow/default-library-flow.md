# Default Library Flow

## Summary

An overview diagram of how Dotkernel's libraries interact with each other during a typical request, showing which packages depend on which and where your application code plugs in.

## Details

The graph below demonstrates a default flow between Dotkernel's libraries.

![Dotkernel API Default Library Flow!](https://docs.dotkernel.org/img/api/v7/dotkernel-library-flow.png)

## FAQ

**Q: What is this diagram useful for?**

A: It gives you a mental map of the stack.
When you need to extend or debug behaviour, the diagram shows which library owns that responsibility so you know where to look first.

**Q: Does the flow include third-party packages?**

A: The diagram focuses on Dotkernel's own `dot-*` libraries and the Mezzio/Laminas components they build on.
See [Packages](../introduction/packages.md) for the full dependency list.

**Q: Where does my own module fit in?**

A: Your modules sit on top of this flow: they consume the services the libraries provide through the DI container.
See [Dependency Injection](../core-features/dependency-injection.md).

**Q: Is there a separate diagram for the request lifecycle?**

A: Yes.
The middleware pipeline is documented separately in [Middleware flow](middleware-flow.md).
