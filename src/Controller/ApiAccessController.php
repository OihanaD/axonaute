<?php

namespace App\Controller;

use App\Service\AxaunoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiAccessController extends AbstractController
{
    #[Route('/api/{id}/{endpoint}', name: 'api_id')]
    public function apiBaseConnexion(AxaunoteService $service, Request $request, $id, $endpoint): Response
    {
        $response = $service->getApiRelation($id, $request, $endpoint);
        return $this->json($response);
    }
}
