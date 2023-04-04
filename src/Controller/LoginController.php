<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginController extends AbstractController
{
    #[Route('api/login', name: 'api_login')]
    private $jwtManager;
    private $managerRegistry;

    public function __construct(JWTTokenManagerInterface $jwtManager, ManagerRegistry $managerRegistry)
    {
        $this->jwtManager = $jwtManager;
        $this->managerRegistry = $managerRegistry;
    }

    public function login(Request $request): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $this->managerRegistry->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
