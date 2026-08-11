# Library Flow for Email

## Summary

A simplified diagram of the libraries involved when Dotkernel API sends an email, from the service that triggers the message through template rendering to the mail transport.

## Details

The graph below demonstrates the simplified flow between Dotkernel's libraries for sending an email.

![Dotkernel API Default Library Flow!](https://docs.dotkernel.org/img/api/v7/dotkernel-library-flow-email.png)

## FAQ

**Q: Which libraries handle email in Dotkernel API?**

A: `dot-mail` handles composing and transporting the message, while the templating layer renders the message body.
See [Rendering and sending emails](../core-features/rendering-and-sending-emails.md).

**Q: Where do I configure the mail transport?**

A: In the mail configuration under `config/autoload/`.
See [Configuration files](../installation/configuration-files.md).

**Q: Can I send email without using the template renderer?**

A: Yes.
The renderer is only needed when the message body comes from a template; you can also set the body directly on the message.

**Q: Why is the email flow shown separately from the default library flow?**

A: Because sending email is a side flow triggered from a service rather than part of the request pipeline.
Keeping it separate makes both diagrams easier to read.
