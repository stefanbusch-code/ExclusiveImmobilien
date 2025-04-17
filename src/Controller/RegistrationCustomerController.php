<?php

namespace App\Controller;

use App\Entity\Customer;
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

final class RegistrationCustomerController extends AbstractController
{
    #[Route('/registration/customer', name: 'app_registration_customer')]
    public function regististrationCustomer(Request $request,UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $regform = $this->createFormBuilder()
            ->add('customername', TextType::class,[
                'label' => 'Kunde',
            ])
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein.',
            ])

            ->add('registrieren', SubmitType::class)
            ->getForm();
        ;

        $regform->handleRequest($request);
        if ($regform->isSubmitted() && $regform->isValid()) {
            $eingabe = $regform->getData();

            $customer = new Customer();
            $customer ->setCustomername($eingabe['customername']);

            $customer ->setPassword($passwordHasher->hashPassword($customer, $eingabe['password']));

            $entityManager->persist($customer);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('app_property_homepage'));

        }

        return $this->render('registration_customer/index.html.twig', [
            'regform' => $regform->createView(),
        ]);
    }
}
