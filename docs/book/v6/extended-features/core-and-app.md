# Core and App code structure

In the 6.0 version, the project is split into two main parts: **App** and **Core**.

The purpose is to reach a headless CMS format for easier scalability.
Headless CMS is a back-end-only content management system that acts primarily as a content repository.
Compared to traditional CMS platforms (e.g WordPress) that tightly couple the front end and back end, a headless CMS decouples the content management from the presentation layer.
The content is delivered through APIs allowing any front-end to fetch and display it making front-end and back-end development easier to work in parallel.

## What is "App" and what is "Core"?

### Core

The **Core** like the backbone of the application.
It's where the core logic lives.

- It handles things like:
    - Authentication
    - Database setup
    - Middleware

You usually don’t touch this unless you’re updating how the system works "behind the scenes."

### App

The **App** is where you build your actual project — the "body" of your application.

- This is where you:
    - Define your routes
    - Write your handlers
    - Add your custom logic
    - Error reporting

If you're building features for the project, you're mostly working here.
