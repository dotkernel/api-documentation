# Rendering and sending emails

In the previous versions of Dotkernel API we have been composing email bodies using Twig from the mezzio/mezzio-twigrenderer package.\
In the current version of Dotkernel API, we introduced the core mail service Core/src/App/src/Service/MailService which is responsible for sending all emails.

Being a core service, MailService is used across all projects implementing the Core architecture.\
To compose and send an email, a solid implementation of TemplateRendererInterface was required to be injected into MailService, because each method rendered and parsed their respective templates in place before sending an email.
This is acceptable with other Dotkernel applications which in most cases return a rendered template, but being that Dotkernel API mostly returns JSON objects, rendered with a different renderer, Twig had to be replaced with a lighter solution.

The solution is a custom Api\App\Template\Renderer implementing Api\App\Template\RendererInterface.
This is a lightweight renderer, aimed at rendering a combination of PHP and HTML files with phtml extension.

With the new solution, MailService requires no implementation of any renderer because it no longer has to render templates internally.\
Instead, an implementation of Api\App\Template\RendererInterface is first injected in the handler:

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
$body = $this->renderer->render('user::welcome', ['user' => $user])
```

And finally, the handler calls the mail service with the composed $body being passed as a parameter to the method which sends the email:

```php
// $user object contains email, firstname and lastname

$this->mailService->sendWelcomeMail($user, $body);
```

>Other Dotkernel applications implementing the Core architecture do the same in the handlers, but keep using Twig as the template renderer.
