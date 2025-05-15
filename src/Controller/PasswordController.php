<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordController extends AbstractController
{
    #[Route('/password/change', name: 'app_password_change')]
    public function changePassword(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'Das alte Passwort ist nicht korrekt');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Passwort wurde geändert!');
                return $this->redirectToRoute('app_password_change');

            }

        }

        return $this->render('password/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



}

