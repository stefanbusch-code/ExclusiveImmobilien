<?php

namespace App\Controller;


use App\Entity\Employee;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationEmployeeController extends AbstractController
{
    #[Route('/registration/employee', name: 'app_registration_employee')]
    public function reistrationEmployee(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $regform = $this->createFormBuilder()
            ->add('email', EmailType::class,[
                'label' => 'Email',
            ])
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
            ])
            ->add('registration', SubmitType::class)
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
            return $this->redirectToRoute('app_property_homepage');
        }

        return $this->render('registration_employee/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }
}
