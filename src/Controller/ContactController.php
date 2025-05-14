<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $name = ($data['name']);
            $user_email = ($data['email']);
            $subject = ($data['subject']);
            $message = ($data['message']);

            $email = (new TemplatedEmail())
                ->from($data['email'])
                ->to('info@exclusive-immobilien.de')
                ->subject($data['subject'])
                ->text($data['message'])

                ->htmlTemplate('contact/mail.html.twig')

                ->context([
                    'name' => $name,
                    'user_email' => $user_email,
                    'subject' => $subject,
                    'message' => $message,
            ]);

            $mailer->send($email);
            $this->addFlash('success', 'Ihre Nachricht wurde erfolgreich gesendet');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
