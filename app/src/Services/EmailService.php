<?php

namespace App\Services;


use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * EmailService constructor.
     *
     * @param MailerInterface $mailer
     * @param UrlGeneratorInterface $router
     */
    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router)
    {
        $this->mailer = $mailer;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @throws TransportExceptionInterface
     */
    public function sendEmailConfirmationMessage(Request $request, User $user): void
    {
        $emailConfirmation = $user->getEmailConfirmations()->first();
        $protocol = $request->isSecure() ? 'https://' : 'http://';
        $domain = $_ENV['APP_DOMAIN'];
        $url = $this->router->generate('emailConfirmation', ['hash' => $emailConfirmation->getHash()]);
        $emailConfirmationUrl = $protocol . $domain . $url;
        $sendEmail = (new TemplatedEmail())
            ->from("admin@$domain")
            ->to($emailConfirmation->getEmail())
            ->subject('Email confirmation')
            ->htmlTemplate('emails/email_confirmation.html.twig')
            ->context([
                'name' => $user->getName(),
                'emailConfirmationUrl' => $emailConfirmationUrl
            ]);

        $this->mailer->send($sendEmail);
    }

    /**
     * @param User $user
     * @param string $token
     *
     * @throws TransportExceptionInterface
     */
    public function sendPasswordRecoveryMessage(User $user, string $token): void
    {
        $domain = $_ENV['APP_DOMAIN'];
        $sendEmail = (new TemplatedEmail())
            ->from("admin@$domain")
            ->to($user->getEmail())
            ->subject('Password recovery')
            ->htmlTemplate('emails/password_recovery.html.twig')
            ->context([
                'name' => $user->getName(),
                'token' => $token
            ]);

        $this->mailer->send($sendEmail);
    }
}