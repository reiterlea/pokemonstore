<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $usersList = $entityManager->getRepository(User::class)->findAll();
        $users = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ];
        }, $usersList);

        return new JsonResponse($users, Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'user')]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $user = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ];

        return new JsonResponse($user, Response::HTTP_OK);
    }


    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['first_name']);
        $user->setUsername($data['username']);
        $user->setLastName($data['last_name']);
        $user->setRoles(['ROLE_USER']);
        $plainPassword = $data['password'];
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plainPassword
        );
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function update($id, EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)->find($id);
        $user->setEmail($data['email']);
        $user->setFirstName($data['first_name']);
        $user->setUsername($data['username']);
        $user->setLastName($data['last_name']);
        $user->setRoles($data['roles']);
        $plainPassword = $data['password'];
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plainPassword
        );
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User updated!'], Response::HTTP_OK);
    }

    
}
