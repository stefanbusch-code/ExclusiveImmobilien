<?php

namespace App\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationEmployeeController extends AbstractController
{
    #[Route('/registration/employee', name: 'app_registration_employee')]
    public function regististrationEmployee(Request $request,UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $regform = $this->createFormBuilder()
            ->add('username', TextType::class,[
                'label' => 'Mitarbeiter',
            ])
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
                ])
            ->add('registration', SubmitType::class)
            ->getForm();
            ;

            $regform->handleRequest($request);
            if ($regform->isSubmitted() && $regform->isValid()) {
                $eingabe = $regform->getData();

                $employee= new Employee();
                $employee ->setEmployeename($eingabe['username']);

                $employee ->setPassword($passwordHasher->hashPassword($employee, $eingabe['password']));

                $entityManager->persist($employee);
                $entityManager->flush();
                return $this->redirect($this->generateUrl('app_property_homepage'));

            }

        return $this->render('registration_employee/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }
}
