<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;




/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailProduct",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getProducts")
 * )
 *
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["getProducts", "getProductsDetails"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["getProducts", "getProductsDetails"])]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le nom du produit doit faire au minimum {{ limit }} caractères", maxMessage: "Le nom du produit ne peut pas faire plus de {{ limit }} caractères")]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Le détail du produit est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le détail du produit doit faire au minimum {{ limit }} caractères", maxMessage: "Le détail du produit ne peut pas faire plus de {{ limit }} caractères")]
    #[Groups(["getProductsDetails"])]
    private $detail;

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
}
