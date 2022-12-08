<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer
    )
    {}

    public function sendEmail(): void
    {
        $email = (new Email())
            ->from(Address::create('Z&L Boutik <noreply@zl-boutik.com>'))
            ->to('amoshermannakre@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Nouvelle Commande')
            //->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

            try {

                $this->mailer->send($email);

            } catch (TransportExceptionInterface $e) {
                // some error prevented the email sending; display an
                // error message or try to resend the message
            }
        // ...
    }
}