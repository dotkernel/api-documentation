# Rendering and sending emails

## Summary

Email bodies are no longer rendered inside the mail service with Twig.
A lightweight custom renderer (`Api\App\Template\Renderer`) renders `phtml` templates in the handler, and the resulting body is passed to the core `MailService`, which is responsible only for sending.

## Details

In the previous versions of Dotkernel API we have been composing email bodies using **Twig** from the `mezzio/mezzio-twigrenderer` package.
In the current version of Dotkernel API, we introduced the core mail service `Core/src/App/src/Service/MailService` which is responsible for sending all emails.

Being a core service, `MailService` is used across all projects implementing the Core architecture.
To compose and send an email, a solid implementation of `TemplateRendererInterface` was required to be injected into `MailService`, because each method rendered and parsed their respective templates in place before sending an email.
This is acceptable with other Dotkernel applications which in most cases return a rendered template, but being that Dotkernel API mostly returns JSON objects, rendered with a different renderer, **Twig** had to be replaced with a lighter solution.

The solution is a custom [`Api\App\Template\Renderer`](https://github.com/dotkernel/api/blob/7.0/src/App/src/Template/Renderer.php) implementing [`Api\App\Template\RendererInterface`](https://github.com/dotkernel/api/blob/7.0/src/App/src/Template/RendererInterface.php).
This is a lightweight renderer, aimed at rendering a combination of **PHP** and **HTML** files with `phtml` extension.

With the new solution, `MailService` requires no implementation of any renderer because it no longer has to render templates internally.
Instead, an implementation of `Api\App\Template\RendererInterface` is first injected in the handler:

```php
class ExampleHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        RendererInterface::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected RendererInterface $renderer,
    ) {
}
```

Then, the handler calls the renderer and saves the rendered template in a variable:

```php
$body = $this->renderer->render('user::welcome', ['user' => $user]);
```

And finally, the handler calls the mail service with the composed $body being passed as a parameter to the method which sends the email:

```php
// $user object contains email, firstname and lastname

$this->mailService->sendWelcomeMail($user, $body);
```

> Other Dotkernel applications implementing the Core architecture do the same in the handlers but keep using Twig as the template renderer.

## FAQ

**Q: Why was Twig replaced?**

A: Dotkernel API mostly returns JSON rendered by a different renderer, so pulling in a full templating engine solely for email bodies was unnecessary weight.
A lightweight renderer for `phtml` files covers the need.

**Q: What template format does the custom renderer use?**

A: Files combining PHP and HTML with the `.phtml` extension.

**Q: Does `MailService` still need a renderer injected?**

A: No.
Rendering happens in the handler, and the finished body is passed to the mail service as a parameter.

**Q: How do I render a template and send it?**

A: Inject `MailService` and `RendererInterface` into your handler, call `$this->renderer->render('user::welcome', [...])`, then pass the result to the relevant `MailService` method.

**Q: What does the `user::welcome` syntax mean?**

A: It is a namespaced template name: `user` is the registered template namespace and `welcome` the template within it.

**Q: Can I keep using Twig?**

A: Other Dotkernel applications on the Core architecture do exactly that — the pattern in the handler is the same, only the renderer differs.
Within Dotkernel API the custom renderer is the default.

**Q: Where is the mail transport configured?**

A: In `config/autoload/mail.local.php`, under the `mail` key.
See [Configuration files](../installation/configuration-files.md).
