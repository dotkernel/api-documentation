# The new handler structure

The new Dotkernel 6.0 version contains some new architectural changes compared to it's older version that uses controllers.
The goal of this update is to implement PSR-15 handlers into Dotkernel API.

## What is a handler?

In Dotkernel 6.0, a "handler" is the piece of code that reacts when a user makes a specific request (like visiting a webpage or submitting a form).
It's basically the "controller" that decides what happens next.

HTTP request handlers are at the core of any web application.
They receive a request, process it and return a response.

Controllers with several actions are fine, but handlers split the code into manageable chunks that make your life a lot easier in the long run.
This follows the first of the [SOLID](https://www.digitalocean.com/community/conceptual-articles/s-o-l-i-d-the-first-five-principles-of-object-oriented-design) principles.

## What is a naming pattern?

A naming pattern helps you organize and quickly identify your files by using relevant strings in file names like:

* What a file refers to.
* The action a file performs.
* How a file relates to other files.
* The author of the file’s contents.
* The creation date or the event the file refers to.

### The naming pattern for Dotkernel Handlers

The naming pattern for our Handlers contains:

* The **method** or verb used by the handler (e.g. GET, POST).
* The **resource** name (e.g. Admin, Account).
* The performed **action** (e.g. CreateForm, List).
* An optional **Form** if the handler returns a form that will perform another action when submitted.
* The string **Handler**.

In this way, the developer can easily figure out the functionality of each handler by looking at its name.

## Mapping of the handlers

The full mapping of the handlers and their current paths and actions can be found in the full [naming convention table](https://docs.dotkernel.org/img/api/v6/naming-convention.png).

[![naming-convention-thumbnail](https://docs.dotkernel.org/img/api/v6/naming-convention-thumbnail.png)](https://docs.dotkernel.org/img/api/v6/naming-convention.png)
