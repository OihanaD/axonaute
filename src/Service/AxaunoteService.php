<?php

namespace App\Service;

use App\Entity\ApiInformation;
use App\Entity\User;
use App\Entity\UserApiInformation;
use App\Repository\ApiInformationRepository;
use App\Repository\UserApiInformationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class AxaunoteService
{
    private $httpClient;
    private $entityManager;

    private $baseUrl;
    private $apiKey;
    private $jwtManager;
    private $tokenStorageInterface;
    private $userRepos;
    private $apiRepos;
    private $userApiRepos;
    private $user;
    private $apiInfos;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorageInterface,
        UserRepository $userRepos,
        ApiInformationRepository $apiRepos,
        UserApiInformationRepository $userApiRepos
        // string $baseUrl,
        // string $apiKey
    ) {
        $this->httpClient = $httpClient;
        //à définir dans le formulaire d'ajout api
        $baseUrl = "https://axonaut.com/api/v2/";
        $this->baseUrl = $baseUrl;
        $apiKey = "07128b3580c2fd221388dee1794a67ea";
        $this->apiKey = $apiKey;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->userRepos = $userRepos;
        $this->apiRepos = $apiRepos;
        $this->userApiRepos = $userApiRepos;

    }

    public function makeApiRequest(string $path)
    {
        //Je prend ma base d'url et j'y rajoute le endpoint pour y avoir accès
        $url = $this->baseUrl . $path;
        //Je prends en coumpte le chemin de l'url et les paramètres de la requête dans le header
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'userApiKey' => $this->apiKey,
            ],
        ]);

        return json_decode(stripslashes($response->getContent()));
    }
    public function getApiRelation($idInPath)
    {
        $idRelation = $this->userApiRepos->find($idInPath);
        $userId= $this->user->getid();
        $user = $this->userApiRepos->findBy(['UserId' => $userId]);
        dd($user);


    }

    //$apiService = new ApiService($httpClient, 'https://api.example.com/', 'your_api_key');
    //$response = $apiService->makeApiRequest('endpoint');

    public function add(Request $request)
    {

        $data = $this->decodeToken($request);
        // dd($this->user->getid());

        //J'envoi tout en bdd
        $api = new ApiInformation();
        $api->setBaseUrl($data['url']);
        $api->setApiKey($data['key']);
        $apiInfo = $this->apiRepos->findOneBy([
            'baseUrl' => $data['url'],
            'ApiKey' => $data['key']
        ]);
        //Je vérifie si les données api exist déjà en BDD
        if ($apiInfo) {
            return new JsonResponse(['message' => 'Api Connexion alredy exist'], 200);
        } else {
            $userapi = new UserApiInformation;
            $userapi->setUserId($this->user);
            $userapi->setApiInformationId($api);


            $this->entityManager->persist($api);
            $this->entityManager->persist($userapi);

            $this->entityManager->flush();


            return new JsonResponse(['message' => 'API URL and key added successfully'], 201);
        }
    }


    public function decodeToken(Request $request)
    {
        //Je récupère le jwt token et je le décode
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());

        if ($decodedJwtToken) {

            //Je prends le userName qui est l'email du jwt
            $userName = $decodedJwtToken['username'];
            //Dans mon repos j'ai créer la méthode findByEmail pour herche les utilisateurs par mail
            $user = $this->userRepos->findByEmail($userName);
            $this->user = $user;

            //Je récupère le body envoyé dans la requête ( url + key)
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['message' => 'Invalid JSON'], 400);
            } else {
                return $data;
            }
        }
    }
}
