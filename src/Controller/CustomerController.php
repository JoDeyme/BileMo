<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
}
