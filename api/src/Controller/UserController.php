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
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    #[OA\Get(
        path: "/api/users",
        summary: "Get all users",
        responses: [new OA\Response(
            response: 200,
            description: "Successful operation",
            content: new OA\JsonContent()
        )]
    )]
    #[Route('/api/users', name: 'get_users', methods: ['GET'])]
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

    #[OA\Get(
        path: "/api/users/{id}",
        summary: "Get a single user by ID",
        parameters: [new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
        )],
        responses: [new OA\Response(
            response: 200,
            description: "Successful operation",
            content: new OA\JsonContent()
        )]
    )]

    #[Route('/api/users/{id}', name: 'get_user', methods: ['GET'])]
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


    #[OA\Post(
        path: "/api/register",
        summary: "Create a new user",
        requestBody: new OA\RequestBody(
            description: "User object that needs to be created",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "email", type: "string"),
                            new OA\Property(property: "first_name", type: "string"),
                            new OA\Property(property: "last_name", type: "string"),
                            new OA\Property(property: "username", type: "string"),
                            new OA\Property(property: "password", type: "string"),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            ),
        ]
    )]
    #[Route('/api/register', name: 'register', methods: ['POST'])]
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

    #[OA\Delete(
        path: "/api/users/{id}",
        summary: "Delete a user",
        parameters: [new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
        )],
        responses: [
            new OA\Response(
                response: 204,
                description: "User deleted"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            ),
        ]
    )]
    #[Route('/api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['status' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }

    #[OA\Put(
        path: "/api/users/{id}",
        summary: "Update a user",
        requestBody: new OA\RequestBody(
            description: "User object that needs to be updated",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "email", type: "string"),
                            new OA\Property(property: "first_name", type: "string"),
                            new OA\Property(property: "last_name", type: "string"),
                            new OA\Property(property: "username", type: "string"),
                            new OA\Property(property: "password", type: "string"),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "User updated"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            ),
        ]
    )]
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
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
