<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["getCustomers", "getUsers","getUsersDetails"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getCustomers", "getUsers","getUsersDetails"])]
    #[Assert\NotBlank(message: "Le nom du client est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom du client doit faire au minimum {{ limit }} caractères", maxMessage: "Le nom du client ne peut pas faire plus de {{ limit }} caractères")]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getCustomers", "getUsers","getUsersDetails"])]
    #[Assert\NotBlank(message: "Le détail du client est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le détail du client doit faire au minimum {{ limit }} caractères", maxMessage: "Le détail du client ne peut pas faire plus de {{ limit }} caractères")]
    private $detail;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customer')]
    #[Groups(["getCustomers"])]
    #[Assert\NotBlank(message: "L'ID de l'utilisateur est obligatoire")]
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
