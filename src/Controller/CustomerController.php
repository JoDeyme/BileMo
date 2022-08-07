<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class CustomerController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer la liste des clients
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne la liste des clients",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomers"}))
     *    )
     * )
     * * @OA\Tag(name="Clients")
     * 
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */


    #[Route('/api/customers', name: 'customers', methods: ['GET'])]
    public function getAllCustomers(CustomerRepository $customerRepository, SerializerInterface $serializerInteface): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $customerList = $customerRepository->findAll();
        } else {
            $customerList = $customerRepository->findBy(['user' => $this->getUser()]);
        }


        $context = SerializationContext::create()->setGroups(["getCustomers"]);
        $jsonCustomerList = $serializerInteface->serialize($customerList, 'json', $context);

        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer le détail d'un client
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne le détail d'un client",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomersDetails"}))
     *    )
     * )
     * * @OA\Tag(name="Clients")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */




    #[Route('/api/customers/{id}', name: 'customer_id', methods: ['GET'])]
    public function getCustomer(Customer $customer, SerializerInterface $serializerInteface, CustomerRepository $customerRepository): JsonResponse
    {

        if ($customer->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Vous n\'avez pas les droits pour accéder à cette donnée'], Response::HTTP_FORBIDDEN);
        }

        $context = SerializationContext::create()->setGroups(["getCustomersDetails"]);
        $jsonCustomer = $serializerInteface->serialize($customer, 'json', $context);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }


    /**
     * Cette méthode permet supprimer un client.
     * 
     * @OA\Response(
     *    response=200,
     *   description="Supprime un client",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomersDetails"}))
     *    )
     * )
     * * @OA\Tag(name="Clients")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */

    #[Route('/api/customers/{id}', name: 'customer_delete', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        if ($customer->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Vous n\'avez pas les droits pour supprimer ce client'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($customer);
        $entityManager->flush();
        $cache->invalidateTags(["customersCache"]);
        return new JsonResponse(['message' => 'Client supprimé'], Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un client.
     * 
     * @OA\Response(
     *    response=200,
     *   description="Crée un client",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomersDetails"}))
     *    )
     * )
     * * @OA\Tag(name="Clients")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */


    #[Route('/api/customers', name: 'customer_create', methods: ['POST'])]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $customer->setUser($this->getUser());

        $error = $validator->validate($customer);
        if (count($error) > 0) {
            foreach ($error as $er) {
                $errorMessage[] = $er->getMessage();
            }
            return new JsonResponse($serializer->serialize($errorMessage, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($customer);
        $entityManager->flush();


        $context = SerializationContext::create()->setGroups(["getCustomers"]);
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        $location = $urlGenerator->generate('customer_id', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /*     #[Route('/api/customers/{id}', name: 'customer_update', methods: ['PUT'])]
    public function updateCustomer(Customer $currentCustomer, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserRepository $userRepository) : JsonResponse
    {
        $updatedCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer]);
    
        $content = $request->toArray();
        $idUser = $content['idUser'] ?? -1;
        $updatedCustomer->setUser($userRepository->find($idUser));

        $entityManager->persist($updatedCustomer);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);    
    
    } */

    /**
     * Cette méthode permet de modifier un client existant.
     * 
     * @OA\Response(
     *    response=200,
     *   description="Modifie un client",
     * @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getCustomersDetails"}))
     *    )
     * )
     * @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="nouveau nom du client",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="detail",
     *     in="query",
     *     description="nouveau detail du client",
     *     @OA\Schema(type="string")
     * )
     *      * @OA\Parameter(
     *     name="idUser",
     *     in="query",
     *     description="nouveau detail du client",
     *     @OA\Schema(type="int")
     * )
     * * @OA\Tag(name="Admin")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */


    #[Route('/api/customers/{id}', name: "customer_update", methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un client')]
    public function updateCustomer(Request $request, SerializerInterface $serializer, Customer $currentCustomer, EntityManagerInterface $entityManager, UserRepository $userRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse
    {
        $newCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        $currentCustomer->setName($newCustomer->getName());
        $currentCustomer->setDetail($newCustomer->getDetail());

        $content = $request->toArray();
        $idUser = $content["idUser"] ?? -1;

        $currentCustomer->setUser($userRepository->find($idUser));

        // On vérifie les erreurs
        $errors = $validator->validate($currentCustomer);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }



        $entityManager->persist($currentCustomer);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Client modifié'], Response::HTTP_OK);

        // On vide le cache.
        $cache->invalidateTags(["customersCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
