<?php

namespace App\Service;

use App\Entity\ApiInformation;
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
    private $jwtManager;
    private $tokenStorageInterface;
    private $userRepos;
    private $apiRepos;
    private $userApiRepos;
    private $user;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorageInterface,
        UserRepository $userRepos,
        ApiInformationRepository $apiRepos,
        UserApiInformationRepository $userApiRepos
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->userRepos = $userRepos;
        $this->apiRepos = $apiRepos;
        $this->userApiRepos = $userApiRepos;
    }

   
    public function getApiRelation($idInPath, Request $request, string $endpoint)
    {
        //Je récupère le token et j'attribut le user
        $this->decodeToken($request);

        //Je recherche la relation avec l'id de la requête = id: relation, id: user, id: api
        $idRelation = $this->userApiRepos->find($idInPath);
        //Je prend l'id user de la relation
        if ($idRelation) {

            $userApi = $idRelation->getUserId();
            //Je récupère le user via la variable user du token
            $user = $this->user;
            //Si le user correspond au user du token
            if ($userApi == $user) {
                //Je récupère la base url et la clé via de la relation// find on by car je suis sûre qu'il n'y en a qu'un
                $apiInfos = $this->apiRepos->findOneBy(["id" => $idRelation->getApiInformationId()]);
                //Je récupère l'url dans la table en la cherchant dans le rops des api information via id de api information danss la table tampon
                $url = $apiInfos->getBaseUrl();
                //Pareil pour la clé
                $key = $apiInfos->getApiKey();
                //Maintenant je peux utiliser mon make api pour faire ma requête
                $response = $this->makeApiRequest($url, $key, $endpoint);
               
                return json_decode(stripslashes($response->getContent()));
               //Changer en try et catch?
            } else {
                return new JsonResponse(['message' => 'Api not found'], 404);
            }
        } else {
            return new JsonResponse(['message' => 'Api not found'], 404);
        }
    }
    public function makeApiRequest(string $url, string $key, string $endpoint)
    {
        //Je prend ma base d'url et j'y rajoute le endpoint pour y avoir accès
        $url = $url . "/" . $endpoint;
        //Je prends en coumpte le chemin de l'url et les paramètres de la requête dans le header
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'userApiKey' => $key,
            ],
        ]);

        return $response;
    }



    public function add(Request $request)
    {

        $data = $this->decodeToken($request);
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
            //J'envoie tout en bdd
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
