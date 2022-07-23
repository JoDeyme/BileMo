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

class CustomerController extends AbstractController
{
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

    #[Route('/api/customers/{id}', name: 'detailCustomer', methods: ['GET'])]
    public function getCustomer(Customer $customer, SerializerInterface $serializerInteface, CustomerRepository $customerRepository): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $customerList = $customerRepository->findBy(['id' => $customer->getId()]);
        } else {
            $customerList = $customerRepository->findBy(['id' => $customer->getId(), 'user' => $this->getUser()]);
        }

        $context = SerializationContext::create()->setGroups(["getCustomersDetails"]);
        $jsonCustomer = $serializerInteface->serialize($customerList, 'json', $context);

        return new JsonResponse($jsonCustomer, Response::HTTP_OK, [], true);
    }


    #[Route('/api/customers/{id}', name: 'customer_delete', methods: ['DELETE'])]
    public function deleteCustomer(Customer $customer, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache): JsonResponse
    {
        $entityManager->remove($customer);
        $entityManager->flush();
        $cache->invalidateTags(["customersCache"]);
        return new JsonResponse(['message' => 'Client supprimé'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/customers', name: 'customer_create', methods: ['POST'])]
    public function createCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, ValidatorInterface $validator): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $content = $request->toArray();
        $idUser = $content['idUser'] ?? -1;
        $customer->setUser($userRepository->find($idUser));

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

        // On vide le cache.
        $cache->invalidateTags(["customersCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
