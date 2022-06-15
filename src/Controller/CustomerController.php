<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    #[Route('/api/customers', name: 'customer', methods: ['GET'])]
    public function getAllCustomers(CustomerRepository $customerRepository, SerializerInterface $serializerInteface) : JsonResponse
    {
       $customerList = $customerRepository->findAll();

        $jsonCustomerList = $serializerInteface->serialize($customerList, 'json', ['groups' => 'getCustomers']);
        
        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/customers/{id}', name: 'customer_id', methods: ['GET'])]
    public function getCustomer(Customer $customer, SerializerInterface $serializerInteface) : JsonResponse
    {
        $jsonCustomer = $serializerInteface->serialize($customer, 'json', ['groups' => 'getCustomers']);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }


    #[Route('/api/customers/{id}', name: 'customer_delete', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager) : JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Client supprimé'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/customers', name: 'customer_create', methods: ['POST'])]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository) : JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $content = $request->toArray();
        $idUser = $content['idUser'] ?? -1;
        $customer->setUser($userRepository->find($idUser));

        $entityManager->persist($customer);
        $entityManager->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomers']);
        $location = $urlGenerator->generate('customer_id', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
    
        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/customers/{id}', name: 'customer_update', methods: ['PUT'])]
    public function updateCustomer(Customer $currentCustomer, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserRepository $userRepository) : JsonResponse
    {
        $updatedCustomer = $serializer->deserialize($request->getContent(), Customer::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCustomer]);
    
        $content = $request->toArray();
        $idUser = $content['idUser'] ?? -1;
        $updatedCustomer->setUser($userRepository->find($idUser));

        $entityManager->persist($updatedCustomer);
        $entityManager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);    
    
    }
}
