<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class ProductController extends AbstractController
{

    /**
     * Cette méthode permet de récupérer la liste des produits.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des produits",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"getProducts"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre de produit que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Products")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'product', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializerInterface, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $jsonProductList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializerInterface) {
            $item->tag("productsCache");
            $productList = $productRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getProducts"]);
            return $serializerInterface->serialize($productList, 'json', $context);
        });

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer le détail d'un produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne le détail d'un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class, groups={"getProductsDetails"}))
     *     )
     * )    
     * @OA\Tag(name="Products")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'detailProduct', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializerInterface): JsonResponse

    {
        $context = SerializationContext::create()->setGroups(["getProductsDetails"]);
        $jsonProduct = $serializerInterface->serialize($product, 'json', $context);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de supprimer produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Supprime un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
     *     )
     * )    
     * @OA\Tag(name="Admin")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants supprimer un produit')]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["productsCache"]);
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Produit supprimé'], Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer produit.
     *
     * @OA\Response(
     *     response=200,
     *     description="Crée un produit",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
     *     )
     * )    
     * @OA\Tag(name="Admin")
     *
     * @param ProductRepository $productRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/api/products', name: 'product_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter un produit')]
    public function createProduct(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $error = $validator->validate($product);
        if (count($error) > 0) {
            return new JsonResponse($serializer->serialize($error, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }


        $entityManager->persist($product);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getProducts"]);
        $jsonProduct = $serializer->serialize($product, 'json', $context);
        $location = $urlGenerator->generate('product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
