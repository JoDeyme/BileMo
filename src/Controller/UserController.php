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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class UserController extends AbstractController
{

    /**
     * Cette méthode permet récupérer la liste des utilisateurs.
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne la liste des utilisateurs",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getUsers"}))
     *    )
     * )
     * * @OA\Tag(name="Admin")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */


    #[Route('/api/users', name: 'User', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializerInteface): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $userList = $userRepository->findAll();
        } else {
            $userList = $userRepository->findBy(['id' => $this->getUser()]);
        }


        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUserList = $serializerInteface->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet récupérer le détail d'un utilisateur.
     * 
     * @OA\Response(
     *    response=200,
     *   description="Retourne le détail d'un utilisateur",
     *  @OA\JsonContent(
     *        type="array",
     *       @OA\Items(ref=@Model(type=Customer::class, groups={"getUsersDetails"}))
     *    )
     * )
     * * @OA\Tag(name="Admin")
     * 
     * @param CustomerRepository $customerRepository
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse
     */


    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getUserById(User $user, SerializerInterface $serializerInteface, UserRepository $userRepository): JsonResponse
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $userList = $userRepository->findBy(['id' => $user->getId()]);
        } else {
            $userList = $userRepository->findBy(['id' => $user->getId(), 'id' => $this->getUser()]);
        }


        $context = SerializationContext::create()->setGroups(["getUsersDetails"]);
        $jsonUser = $serializerInteface->serialize($userList, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
