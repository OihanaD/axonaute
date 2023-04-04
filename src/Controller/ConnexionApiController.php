<?php

namespace App\Controller;

use App\Service\AxaunoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConnexionApiController extends AbstractController
{
    #[Route('/api/me/add', name: 'api_add', methods: ['POST', 'PATCH'], format: "json")]
    public function add(AxaunoteService $service,Request $request)
    {
        $response = $service->add($request);
        return $this->json($response);
    }
}
