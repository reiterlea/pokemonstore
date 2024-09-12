<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use App\Entity\Order;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AbstractController
{
    #[OA\Get(
        path: "/api/orders",
        summary: "Get all orders",
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
    #[Route('/orders', name: 'get_orders')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $orderList = $entityManager->getRepository(Order::class)->findAll();
        $orders = array_map(function ($order) {
            return [
                'id' => $order->getId(),
                'user_id' => $order->getUserId(),
                'completed_on' => $order->getCompletedOn(),
                'total' => $order->getTotal(),
                'products' => $order->getProducts()
            ];
        }, $orderList);
        return new JsonResponse($orders, Response::HTTP_OK);
    }

    #[OA\Get(
        path: "/api/order/{id}",
        summary: "Get a single order by ID",
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
    #[Route('/api/order/{id}', name: 'get_order_by_id', methods: ['GET'])]
    public function show($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $order = [
            'id' => $order->getId(),
            'user_id' => $order->getUserId(),
            'completed_on' => $order->getCompletedOn(),
            'total' => $order->getTotal(),
            'products' => $order->getProducts()
        ];

        return new JsonResponse($order, Response::HTTP_OK);
    }
    #[Route('/api/order', name: 'create_order', methods: ['GET'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $order = new Order();
        $order->addUserId($data['user_id']);
        $order->addProduct($data['products']);
        $order->setTotal($data['total']);
        $order->setCompletedOn($data['completed_on']);
        $entityManager->persist($order);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Order created!'], Response::HTTP_CREATED);
    }
}
