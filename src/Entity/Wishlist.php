<?php

namespace App\Entity;

use App\Repository\WishlistRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "wishlists",cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: Property::class, inversedBy: "wishlists",cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Property $property = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;
        return $this;
    }
    public function getProperty(): ?Property
    {
        return $this->property;
    }
    public function setProperty(?Property $property): static
    {
        $this->property = $property;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

}
