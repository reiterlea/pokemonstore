<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Cart;


class CartController extends AbstractController
{
    #[OA\Get(
        path: "/api/cart",
        summary: "Get all carts",
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            )
        ]
    )]
    #[Route('/api/cart', name: 'app_cart', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $cartList = $entityManager->getRepository(Cart::class)->findAll();
        $carts = array_map(function ($cart) {
            return [
                'id' => $cart->getId(),
                'user_id' => $cart->getUserId(),
                'products' => $cart->getProducts(),
                'created_at' => $cart->getCreatedAt(),
            ];
        }, $cartList);
        return new JsonResponse($carts, Response::HTTP_OK);
    }

    #[OA\Get(
        path: "/api/cart/{id}",
        summary: "Get a single cart by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 404,
                description: "Cart not found"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            )
        ]
    )]
    #[Route('/api/cart/{id}', name: 'get_cart_id', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $cart = $entityManager->getRepository(Cart::class)->find($id);
        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }
        $cart = [
            'id' => $cart->getId(),
            'user_id' => $cart->getUserId(),
            'products' => $cart->getProducts(),
            'created_at' => $cart->getCreatedAt(),
        ];
        return new JsonResponse($cart, Response::HTTP_OK);
    }

    #[OA\Post(
        path: "/api/cart",
        summary: "Create a new cart",
        requestBody: new OA\RequestBody(
            description: "Cart object",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "user_id",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "products",
                                type: "array",
                                items: new OA\Items(
                                    ref: "#/components/schemas/Product"
                                )
                            ),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Cart created",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 400,
                description: "Invalid input"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            )
            
        ]
    )]
    #[Route('/api/cart', name: 'create_cart', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $cart = new Cart();
        $cart->setUserId($data['user_id']);
        $cart->addProduct($data['products']);
        $cart->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($cart);
        $entityManager->flush();
        return new JsonResponse(['id' => $cart->getId()], Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: "/api/cart/{id}",
        summary: "Update a cart",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Cart object",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "user_id",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "products",
                                type: "array",
                                items: new OA\Items(
                                    ref: "#/components/schemas/Product"
                                )
                            ),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Cart updated",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 400,
                description: "Invalid input"
            ),
            new OA\Response(
                response: 404,
                description: "Cart not found"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            )
        ]
    )]
    #[Route('/api/cart/{id}', name: 'update_cart', methods: ['PUT'])]
    public function update($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $cart = $entityManager->getRepository(Cart::class)->find($id);
        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }
        $cart->setUserId($data['user_id']);
        $cart->addProduct($data['products']);
        $entityManager->flush();
        return new JsonResponse(['status' => 'Cart updated!'], Response::HTTP_OK);
    }

    #[OA\Delete(
        path: "/api/cart/{id}",
        summary: "Delete a cart",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cart deleted",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 404,
                description: "Cart not found"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            )
        ]
    )]
    #[Route('/api/cart/{id}', name: 'delete_cart', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $cart = $entityManager->getRepository(Cart::class)->find($id);
        if (!$cart) {
            return new JsonResponse(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($cart);
        $entityManager->flush();
        return new JsonResponse(['status' => 'Cart deleted'], Response::HTTP_OK);
    }
}
