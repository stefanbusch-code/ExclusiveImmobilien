<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Employee;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class RegistrationUserController extends AbstractController
{
    #[Route('/registration/user', name: 'app_registration_user')]
    public function registrationUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager,VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer): Response
    {
        $regform = $this->createFormBuilder()
            ->add('email', EmailType::class,[
                'label' => 'Email',
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
            ])
            ->add('registration', SubmitType::class)
            ->getForm()

            ;
        $regform->handleRequest($request);
        if($regform->isSubmitted() && $regform->isValid()) {
            $eingabe = $regform->getData();

            $user = new User();
            $user->setEmail($eingabe['email']);
            $user->setRoles(['ROLE_CUSTOMER']);
            $user->setPassword($passwordHasher->hashPassword($user, $eingabe['password']));

            $customer = new Customer();
            $customer->setUser($user);
            $user->setCustomer($customer);

            $entityManager->persist($customer);
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
                ->subject('Bitte bestätige deine E-Mail_adress')
                ->htmlTemplate('verify_email/email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                ]);

            $mailer->send($email);

            return $this->redirectToRoute('app_property_homepage');
        }

        return $this->render('registration_user/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $userRepository->find($request->get('id'));
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        }
        catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());

            return $this->redirectToRoute('app_registration_user');
        }
            $user->setIsVerified(true);
            $entityManager->flush();

            $this->addFlash('success', 'E-Mail gesendet.');
            return $this->redirectToRoute('app_login');

    }



}
