<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CustomerDataController extends AbstractController
{
    #[IsGranted('ROLE_CUSTOMER')]
    #[Route('/customer/data', name: 'app_customer_data')]
    public function showData(): Response
    {
        /** @var Customer $customer */
        $customer = $this->getUser();

        return $this->render('customer_data/index.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/customer/data/edit', name: 'app_customer_data_edit')]
    public function editProfil(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var \App\Entity\Customer $customer */
        $customer = $this->getUser();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            if (!empty($newPassword)) {
                $password = $passwordHasher->hashPassword($customer, $newPassword);
                $customer->setPassword($password);
            }

            $newEmail = $form->get('email')->getData();
            if (!empty($newEmail)) {
                $customer->setEmail($newEmail);
            }
            $newPhone = $form->get('phone')->getData();
            if (!empty($newPhone)) {
                $customer->setPhone($newPhone);
            }

            $firstname = $form->get('firstname')->getData();
            if (!empty($firstname)) {
                $customer->setFirstname($firstname);
            }
            $lastname = $form->get('lastname')->getData();
            if (!empty($lastname)) {
                $customer->setLastname($lastname);
            }
            $street = $form->get('street')->getData();
            if (!empty($street)) {
                $customer->setStreet($street);
            }
            $streetnumber = $form->get('streetnumber')->getData();
            if (!empty($streetnumber)) {
                $customer->setStreetnumber($streetnumber);
            }
            $zipcode = $form->get('zipcode')->getData();
            if (!empty($zipcode)) {
                $customer->setZipcode($zipcode);
            }
            $city = $form->get('city')->getData();
            if (!empty($city)) {
                $customer->setCity($city);
            }
            $state = $form->get('state')->getData();
            if (!empty($state)) {
                $customer->setState($state);
            }
            $country = $form->get('country')->getData();
            if (!empty($country)) {
                $customer->setCountry($country);
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success','Profil erfolgreich aktualisiert');
            return $this->redirectToRoute('app_customer_data');
        }
        return $this->render('customer_data/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
