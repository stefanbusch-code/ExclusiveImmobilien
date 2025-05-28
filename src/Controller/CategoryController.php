<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function createCategory(EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $category->setDiscription('Apartments zum Mieten');

        $entityManager->persist($category);
        $entityManager->flush();

        return new Response('Saved new Category with id ' .$category->getId());
    }
}
