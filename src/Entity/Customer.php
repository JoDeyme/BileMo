<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["getCustomers", "getUsers"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getCustomers", "getUsers"])]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getCustomers", "getUsers"])]
    private $detail;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customer')]
    #[Groups(["getCustomers"])]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
