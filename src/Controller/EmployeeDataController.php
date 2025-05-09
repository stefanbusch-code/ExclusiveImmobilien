<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Form\EmployeeType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EmployeeDataController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYEE')]
    #[Route('/employee/data', name: 'app_employee_data')]
    public function showData(): Response
    {
        $user = $this->getUser();
        $employee = $user->getEmployee();
        $email = $user->getEmail();

        return $this->render('employee_data/index.html.twig', [
            'employee' => $employee,
            'email' => $email,
        ]);
    }

    #[Route('/employee/data/edit', name: 'app_employee_data_edit')]
        public function editProfile(EntityManagerInterface $entityManager, Request $request, ): Response
        {
            /**@var Employee $employee */
            $employee = $this->getUser()->getEmployee();


            $form = $this->createForm(EmployeeType::class, $employee);
            $form ->handleRequest($request);

            If ($form->isSubmitted() && $form->isValid()) {

                $newFirstname =$form->get('firstname')->getData();
                if (!empty($newFirstname)){
                    $employee->setFirstname($newFirstname);
                }

                $newLastname =$form->get('lastname')->getData();
                if (!empty($newLastname)){
                    $employee->setLastname($newLastname);
                }

                $newPhone =$form->get('phone')->getData();
                if (!empty($newPhone)){
                    $employee->setPhone($newPhone);
                }

                $entityManager->persist($employee);
                $entityManager->flush();

                $this->addFlash('success', 'Profil erfolgreich aktualisiert');
                return $this->redirectToRoute('app_employee_data');

            }
            return $this->render('employee_data/edit.html.twig', [
                'form' => $form->createView(),
            ]);
        }

}
