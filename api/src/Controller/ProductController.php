<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Request;


class ProductController extends AbstractController
{
    #[OA\Get(
        path: "/api/products",
        summary: "Get all products",
        responses: [new OA\Response(
            response: 200,
            description: "Successful operation",
            content: new OA\JsonContent()
        ), 
        new OA\Response(
            response: 500,
            description: "Internal error"
        )]
    )]
    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $productList = $entityManager->getRepository(Product::class)->findAll();
        $products = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
            ];
        }, $productList);

        return new JsonResponse($products, Response::HTTP_OK);
    }

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Get a single product by ID",
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
    #[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        $product = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
        ];

        return new JsonResponse($product, Response::HTTP_OK);
    }

    #[OA\Post(
        path: "/api/products",
        summary: "Create a new product",
        requestBody: new OA\RequestBody(
            description: "Product object that needs to be added to the store",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "name",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "description",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "price",
                                type: "number"
                            ),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Product created"
            ),
            new OA\Response(
                response: 500,
                description: "Internal error"
            ),
        ]
    )]
    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product created!'], Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: "/products/{id}",
        summary: "Update an existing product",
        parameters: [new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
        )],
        requestBody: new OA\RequestBody(
            description: "Product object that needs to be updated in the store",
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(
                                property: "name",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "description",
                                type: "string"
                            ),
                            new OA\Property(
                                property: "price",
                                type: "number"
                            ),
                        ]
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Product updated"
            ), new OA\Response(
                response: 500,
                description: "Internal error"
            ),
            new OA\Response(
                response: 404,
                description: "Product not found"
            )
        ]
    )]
    #[Route('/products/{id}', name: 'update_product', methods: ['PUT'])]
    public function update($id, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['name']) ? true : $product->setName($data['name']);
        empty($data['description']) ? true : $product->setDescription($data['description']);
        empty($data['price']) ? true : $product->setPrice($data['price']);
        empty($data['quantity']) ? true : $product->setQuantity($data['quantity']);

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product updated!'], Response::HTTP_OK);
    }
}
