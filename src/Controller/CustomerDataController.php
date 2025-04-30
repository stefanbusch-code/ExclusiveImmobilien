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

            if ($newPassword) {
                $password = $passwordHasher->hashPassword($customer, $newPassword);
                $customer->setPassword($password);
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
