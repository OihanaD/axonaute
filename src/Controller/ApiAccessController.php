<?php

namespace App\Controller;

use App\Service\AxaunoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiAccessController extends AbstractController
{
    #[Route('/api/me/1', name: 'api_id')]
    public function apiBaseConnexion(AxaunoteService $service, Request $request): Response
    {
        $response = $service->getApiRelation(1, $request);
        return $this->json($response);
    }
}
