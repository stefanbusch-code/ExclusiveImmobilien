<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class WishlistController extends AbstractController
{
    #[isGranted('ROLE_CUSTOMER')]
    #[Route('/wishlist', name: 'app_wishlist')]
    public function wishlist(WishlistRepository $wishlistRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();
        $wishlists = $wishlistRepository->findBy(['customer' => $customer]);


        return $this->render('wishlist/index.html.twig', [
            'wishlists' => $wishlists,
        ]);

    }
    #[Route('/wishlist/add/{id}', name:'app_wishlist_add')]
    public function addWishlist(Property $property, WishlistRepository $wishlistRepository, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();

        $existing = $wishlistRepository->findOneBy(['customer' => $customer, 'property' => $property]);
        if (!$existing)
        {
            $wishlist = new Wishlist();
            $wishlist->setCustomer($customer);
            $wishlist->setProperty($property);
            $em->persist($wishlist);
            $em->flush();
        }
        return $this->redirectToRoute('app_wishlist');
    }

    #[Route('/wishlist/delete/{id}', name:'app_wishlist_delete')]
    public function deleteWishlist(Wishlist $wishlist, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $customer = $user->getCustomer();

        if($wishlist->getCustomer() === $customer)
        {
           $em->remove($wishlist);
           $em->flush();
        }
        return $this->redirectToRoute('app_wishlist');
    }

}
