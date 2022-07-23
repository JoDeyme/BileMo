<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'User', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializerInteface) : JsonResponse
    {
       $userList = $userRepository->findAll();
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUserList = $serializerInteface->serialize($userList, 'json', $context);
        
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getUserById(User $user, SerializerInterface $serializerInteface) : JsonResponse
    {
        $context = SerializationContext::create()->setGroups(["getUsersDetails"]);
        $jsonUser = $serializerInteface->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
