<?php

namespace App\Controller;

use App\Service\AxaunoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    
    #[Route('/api/{endpoint}', name: 'app_api')]
    public function endpoint(AxaunoteService $service, string $endpoint): Response
    {
        $response = $service->makeApiRequest($endpoint);
        return $this->json($response);
    }
    #[Route('/api/1', name: 'api_id')]
    public function apiBaseConnexion(AxaunoteService $service, string $endpoint): Response
    {
        $response = $service->getApiRelation(1);
        return $this->json($response);
    }

    
}
