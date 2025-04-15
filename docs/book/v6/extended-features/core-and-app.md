# Core and App code structure

In the 6.0 version, the project is split into two main parts: **App** and **Core**.

## What is "App" and what is "Core"?

### Core

The **Core** like the engine of a car. It's where the core logic lives.

- It handles things like:
    - Authentication
    - Database setup
    - Middleware

You usually don’t touch this unless you’re updating how the system works "behind the scenes".

### App

The **App** is where you build your actual project — the "body" of your application.

- This is where you:
    - Define your routes
    - Write your handlers
    - Add your custom logic
    - Error reporting

If you're building features for the project, you're mostly working here.
