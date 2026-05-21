# Core and App code structure

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
