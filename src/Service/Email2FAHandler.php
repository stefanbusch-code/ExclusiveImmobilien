<?php

namespace App\Service;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Email2FAHandler implements AuthCodeMailerInterface
{
    public function __construct(private MailerInterface $mailer, private string $senderEmail)
    {

    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $authCode = $user->getEmailAuthCode();

        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to($user->getEmailAuthRecipient())
            ->subject('Ihr Bestätigungscode')
            ->htmlTemplate('security/2fa_email_code.html.twig')
            ->context([
                'code' => $authCode,
                'user' => $user,
            ]);

        $this->mailer->send($email);
    }
}