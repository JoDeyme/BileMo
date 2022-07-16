<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'product', methods: ['GET'])]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {   
        $page=$request->get('page', 1);
        $limit=$request->get('limit', 5);

        $idCache="getAllProducts-" . $page . "-" . $limit;

        $jsonProductList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit, $serializer) {
            $item->tag("productsCache");
            $productList = $productRepository->findAllWithPagination($page, $limit);
            return $serializer->serialize($productList, 'json', ['groups' => 'getProducts']);
        });
        
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product_detail', methods: ['GET'])]
    public function getProduct(Product $product, SerializerInterface $serializerInteface): JsonResponse
 
    {   
        $jsonProduct = $serializerInteface->serialize($product, 'json');

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants supprimer un produit')]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $cache->invalidateTags(["productsCache"]);
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Produit supprimé'], Response::HTTP_NO_CONTENT);
    }

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

        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProducts']);
        $location = $urlGenerator->generate('product_detail', ['id' => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }


}
