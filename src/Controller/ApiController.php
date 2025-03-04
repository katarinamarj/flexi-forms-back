<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/message', name: 'api_message', methods: ['GET'])]
    public function getMessage(): JsonResponse
    {
        return $this->json(['message' => 'Hello from Symfony API!']);
    }
}
