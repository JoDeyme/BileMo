<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'User', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializerInteface) : JsonResponse
    {
       $userList = $userRepository->findAll();

        $jsonUserList = $serializerInteface->serialize($userList, 'json', ['groups' => 'getUsers']);
        
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'user_id', methods: ['GET'])]
    public function getUserById(User $user, SerializerInterface $serializerInteface) : JsonResponse
    {
        $jsonUser = $serializerInteface->serialize($user, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}