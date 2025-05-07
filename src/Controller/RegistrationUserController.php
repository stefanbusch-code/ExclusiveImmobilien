<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Employee;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationUserController extends AbstractController
{
    #[Route('/registration/user', name: 'app_registration_user')]
    public function registrationUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $regform = $this->createFormBuilder()
            ->add('email', EmailType::class,[
                'label' => 'Email',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Kunde' => 'ROLE_CUSTOMER',
                    'Mitarbeiter' => 'ROLE_EMPLOYEE',
                    'Admin' => 'ROLE_ADMIN',
                ],

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

            $user =new User();
            $user->setEmail($eingabe['email']);

            $user->setRoles([$eingabe['roles']]);

            $user->setPassword($passwordHasher->hashPassword($user, $eingabe['password']));

            if($eingabe['roles'] === 'ROLE_CUSTOMER') {
                $customer = new Customer();
                $customer->setUser($user);
                $user->setCustomer($customer);
                $entityManager->persist($customer);
            }
            elseif ($eingabe['roles'] === 'ROLE_EMPLOYEE') {
                $employee = new Employee();
                $employee->setUser($user);
                $user->setEmployee($employee);
                $entityManager->persist($employee);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_property_homepage');
        }

        return $this->render('registration_user/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }



}
