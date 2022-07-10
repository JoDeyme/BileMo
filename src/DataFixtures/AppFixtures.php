<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Creation d'un admin
        $userAdmin = new User();
            $userAdmin->setName("Admin");
            $userAdmin->setUsername("admin");
            $userAdmin->setRoles(["ROLE_ADMIN"]);
            $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
            $manager->persist($userAdmin);
        //Creation des fixtures d'utilisateurs
        $listUser = [];
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setName('User' . $i);
            $user->setUsername('utilisateur' . $i);
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $manager->persist($user);
            // On sauvegarde l'utilisateur créé dans un tableau
            $listUser[] = $user;
        }

        //Creation des fixtures de clients
        for ($i = 0; $i < 20; $i++) {
            $costumer = new Customer();
            $costumer->setName('Client' . $i);
            $costumer->setDetail('detail' . $i);
            $costumer->setUser($listUser[array_rand($listUser)]);
            $manager->persist($costumer);
            // On sauvegarde le client créé dans un tableau
        }

        //Creation des fixtures de produits
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('Product '.$i);
            $product->setDetail('Detail '.$i);
            $manager->persist($product);
        }
        
        $manager->flush();
    }
}
