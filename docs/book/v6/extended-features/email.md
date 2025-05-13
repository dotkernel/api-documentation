# Email sending and content parsing

In the previous version of Dotkernel API we have been using the `mezzio/mezzio-twigrenderer` package which added unnecessary complexity to the email sending in our API platform since APIs returns JSON data, not HTML.
Besides this, it used two services (`AdminService` and `UserService`) to send emails.
It was not necessarily wrong, but their job should be only to manage Admin/User accounts.

In order to fix this those problems, we have come up with a lighter custom solution.
Now each project can prepare the bodies of the emails by using its preferred template renderer.
`Core/src/App/src/Service/MailService` is now decoupled by injecting the pre-rendered email body when calling its methods.

Example from `Core/src/App/src/Service/MailService.php`:

```php
<?php

declare(strict_types=1);

namespace Core\App\Service;

use Core\App\Message;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Log\LoggerInterface;
use Dot\Mail\Exception\MailException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use function sprintf;

class MailService
{
    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'dot-mail.service.default',
        'dot-log.default_logger',
        'config',
    )]
    public function __construct(
        protected \Dot\Mail\Service\MailService $mailService,
        protected LoggerInterface $logger,
        private readonly array $config,
    ) {
    }

    /**
     * @throws MailException
     */
    public function sendActivationMail(User $user, string $body): bool
    {
        if ($user->isActive()) {
            return false;
        }

        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->setBody($body);

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getEmail()));
        }
    }
}
```

Rending example from `src/User/src/Handler/PostUserResourceHandler.php`:

```php
if ($user->isPending()) {
    $this->mailService->sendActivationMail(
        $user,
        $this->renderer->render('user::activate', ['user' => $user])
    );
}
```

In this case we are using the `phtml` template from `src/User/src/templates`.
It has a lighter format compared to `twig`.
It is then rendered before sending the activation email by our custom renderer from `src/App/src/Template/Rederer.php`.
The other applications that use the Core structure such as [Dotkernel Admin](https://docs.dotkernel.org/admin-documentation/) use `mezzio/mezzio-twigrenderer` for this purpose.
