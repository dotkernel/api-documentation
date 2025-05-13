# Email sending and content parsing

In the previous version of Dotkernel API we have been using the `mezzio/mezzio-twigrenderer` package which added unnecessary complexity to the email sending in our API platform since APIs returns JSON data, not HTML.
In order to fix this issue, we have come up with a lighter custom solution.
Now each project can prepare the bodies of the emails by using its preferred template renderer.
`Core\App\MailService` is now decoupled by injecting the pre-rendered email body when calling its methods.

Example from `src/User/src/Handler/PostUserResourceHandler.php`:
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

