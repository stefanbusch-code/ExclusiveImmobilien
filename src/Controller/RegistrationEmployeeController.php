<?php

namespace App\Controller;


use App\Entity\Employee;
use App\Entity\User;
use App\Form\SendRegistrationLinkType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;

final class RegistrationEmployeeController extends AbstractController
{
    #[Route('/registration/employee', name: 'app_registration_employee')]
    public function reistrationEmployee(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer): Response
    {
        $email = $request->query->get('email');

        $regform = $this->createFormBuilder(['email' => $email])
            ->add('email', EmailType::class,[
                'label' => 'Email',
                'data' => $email,
                'attr' => ['readonly' => true],

            ])
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
            ])
            ->getForm()

            ;
        $regform->handleRequest($request);
        if ($regform->isSubmitted() && $regform->isValid()) {
            $eingabe = $regform->getData();

            $user = new User();
            $user->setEmail($eingabe['email']);
            $user->setRoles(['ROLE_EMPLOYEE']);
            $user->setPassword($passwordHasher->hashPassword($user, $eingabe['password']));

            $employee = new Employee();
            $employee->setUser($user);
            $user->setEmployee($employee);

            $entityManager->persist($employee);
            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );
            $email = (new TemplatedEmail())
                ->from(new Address('verify_email@exclusiveimmobilien.com', 'Email Verifizierung'))
                ->to((string) $user->getEmail())
                ->subject('Bitte bestätige deine E-Mail-Adresse')
                ->htmlTemplate('verify_email/email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                ]);

            $mailer->send($email);

            return $this->redirectToRoute('app_property_homepage');
        }

        return $this->render('registration_employee/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }

    #[Route('/registration/sendregistrationlink', name:'app_registration_sendregistrationlink')]
    public function sendRegistrationLink(Request $request, MailerInterface $mailer): Response
    {
        $inviteForm = $this->createForm(SendRegistrationLinkType::class);
        $inviteForm->handleRequest($request);

        if ($inviteForm->isSubmitted() && $inviteForm->isValid()) {
            /** @var string $email */
            $email = $inviteForm->get('email')->getData();

            return $this->sendRegistrationLinkEmail($email, $mailer
            );
        }

        return $this->render('registration_employee/invite_link.html.twig', [
            'inviteForm' => $inviteForm,
        ]);

    }

    private function sendRegistrationLinkEmail(string $email, MailerInterface $mailer): Response
    {
        $url = $this->generateUrl('app_registration_employee', [
            'email' => $email,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('noreply@exclusiveimmobilien.com', 'Mitarbeiter Einladung'))
            ->to($email)
            ->subject('Du wurdest eingeladen dich zu registrieren')
            ->htmlTemplate('registration_employee/email.html.twig')
            ->context([
                'registrationUrl' => $url,
            ]);

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Die Einladung wurde erfolgreich versendet.');

        return $this->redirectToRoute('app_registration_sendregistrationlink');
    }


}
