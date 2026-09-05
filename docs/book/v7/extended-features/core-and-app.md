# Core and App code structure

## Summary

From version 6.0 onward the project is split into **Core**, which holds the low-level business logic and infrastructure, and **App**, where you build your own features.
The split supports a Headless Platform architecture and keeps project-specific code separate from the framework's foundations.

## Details

Since version 6.0, the project is split into two main parts: **App** and **Core**.

When you start a new project, there are chances that the requirements are not defined well.
Because of that, your platform needs to be flexible and allow growth in the long term.

Our purpose is to reach a **Headless Platform** architecture for easier scalability.

> The Headless Platform is a backend system that provides data and functionality via an API, completely decoupled from any frontend presentation layer.
> Unlike monolithic platforms like WordPress that bundle the backend and frontend together, a Headless Platform separates content delivery from the presentation layer.
> The content is delivered through APIs allowing any frontend to fetch and display it, which also enables working in parallel on the backend and potentially multiple frontends.

## What is "App" and what is "Core"?

### Core

The **Core** is the backbone of the application.
It contains the core business logic, the lowest-level features.

- It handles things like:
    - Authentication
    - Database setup
    - Middleware

You usually don’t touch this unless you’re updating how the system works "behind the scenes."

### App

The **App** is where you build your actual project — the "body" of your application.

- This is where you will:
    - Define your routes
    - Write your handlers
    - Add your custom logic
    - Error reporting

If you're building features for the project, you're mostly working here.

## FAQ

**Q: Which part should my new feature go into?**

A: App.
Put routes, handlers and feature-specific logic there, and only touch Core when you need to change how the system works underneath.

**Q: Why was the codebase split this way?**

A: To keep project code decoupled from the platform's foundations, so requirements can change without rewriting infrastructure.
This is what makes a Headless Platform architecture practical.

**Q: What does "Headless Platform" mean here?**

A: A backend that exposes data and functionality purely through an API, with no bundled frontend.
Any number of frontends can consume it, and backend and frontend work can proceed in parallel.

**Q: Can Core code depend on App code?**

A: No — the dependency runs one way.
App builds on Core; Core must remain independent of any particular project's features.

**Q: Where do entities and repositories live?**

A: Shared, low-level persistence concerns belong in Core, while entities and repositories specific to your own modules belong in App.
See [File structure](../introduction/file-structure.md).

**Q: Was this split present before version 6.0?**

A: No.
It was introduced in 6.0 when common logic was moved into the Core module.
See [Upgrading from 5.x to 6.0](../upgrading/UPGRADE-6.0.md).
