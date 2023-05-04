<?php

namespace App\Service;

use App\Entity\ApiInformation;
use App\Entity\UserApiInformation;
use App\Repository\ApiInformationRepository;
use App\Repository\UserApiInformationRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        if ($idRelation && !is_null($idRelation) && !empty($idRelation) && isset($idRelation)) {

            $userApi = $idRelation->getUserId();
            //Je récupère le user via la variable user du token
            $user = $this->user;
            //Si le user correspond au user du token
            if ($userApi == $user) {
                try {
                    //Je récupère la base url et la clé via de la relation// find on by car je suis sûre qu'il n'y en a qu'un
                    $apiInfos = $this->apiRepos->findOneBy(["id" => $idRelation->getApiInformationId()]);
                    //Je récupère l'url dans la table en la cherchant dans le rops des api information via id de api information danss la table tampon
                    $url = $apiInfos->getBaseUrl();
                    //Pareil pour la clé
                    $key = $apiInfos->getApiKey();
                    //Maintenant je peux utiliser mon make api pour faire ma requête
                    $response = $this->makeApiRequest($url, $key, $endpoint);
                } catch (\Exception $e) {
                    return new JsonResponse(['message' => 'Api not found'], 404);
                }

                return json_decode(stripslashes($response->getContent()));
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
    public function getCaPerMonth($idInPath, Request $request, $month, $year,$status)
    {
        //J'initialise le tableaux
        $value = [];
        
        //je définis le status de la demande
        $status = $this->getStatus($status);

        $endpoint =  'invoices?date_before=31%2F' . $month . '%2F' . $year . '&date_after=01%2F' . $month . '%2F' . $year."is_paid=".$status;
        //Je récupère les données dans l'url
        $response = $this->getApiRelation($idInPath, $request,$endpoint);
        //Pour chaques valeurs, je récupère le total de la facture et je sotck tout d'ans un tableau
        foreach ($response as $res) {
            $value[] = $res->total;
        }
        //Je sock la somme du tableau dans une variable 
        $kpi = "Ca-03 :" . array_sum($value);
        $responses = new Response();
        //Je revoie la réponse sous forme de json
        $responses->setContent(json_encode($kpi));
        return $responses;
    }
    public function getCaPerYear($idInPath, Request $request, $year)
    {

        $value = [];

        $response = $this->getApiRelation($idInPath, $request, 'invoices?date_before=31%2F12%2F' . $year . '&date_after=01%2F01%2F' . $year);
        foreach ($response as $res) {
            $value[] = $res->total;
        }
        $kpi = "Ca" . $year . " :" . array_sum($value);
        $responses = new Response();
        $responses->setContent(json_encode($kpi));
        return $responses;
    }
    public function getCaPerDate($idInPath, Request $request, $firstYear, $secondYear, $firstMonth, $secondMonth, $firstDay, $secondDay)
    {

        $value = [];

        $response = $this->getApiRelation($idInPath, $request, 'invoices?date_before=' . $secondDay . '%2F' . $secondMonth . '%2F' . $secondYear . '&date_after=' . $firstDay . '%2F' . $firstMonth . '%2F' . $firstYear);
        foreach ($response as $res) {
            $value[] = $res->total;
        }
        $kpi = "Ca-du-" . $firstDay . "-" . $firstMonth . "-" . $firstYear . "au" . $secondDay . "-" . $secondMonth . "-" . $secondYear . ":" . array_sum($value);
        $responses = new Response();
        $responses->setContent(json_encode($kpi));
        return $responses;
    }
    private function getStatus($status)
    {
        if ($status == "facture") {
            return $status = 'true';
        } else if ($status == "commande") {
            return $status = 'false';
        }
    }
    public function getMargeProduct($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"quotations");
        foreach($responses as $response){
            $value[]=$response->margin;

        }
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
    public function getNumberInvoices($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"invoices");
        foreach($responses as $response){
            $value[]=$response;

        }
        $responses = new Response();
        $responses->setContent(json_encode(count($value)));
        return $responses;
    }
    public function getNumberOpportunity($idInPath,$request, $firstYear, $secondYear, $firstMonth, $secondMonth, $firstDay, $secondDay){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"opportunities");
        $firstDate = $firstYear."-".$firstMonth."-".$firstDay;
        $secondDate =  $secondYear."-".$secondMonth."-".$secondDay;
        foreach($responses as $response){
            
            if($response->due_date >= $firstDate  && $response->due_date <= $secondDate){
                $value[]=$response;
            }
            
        }
        
        $responses = new Response();
        $responses->setContent(json_encode(count($value)));
        return $responses;
    }
    public function getNumberOpportunitys($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"opportunities");
        
        foreach($responses as $response){
            
            $value = [];

            // jee parcour les réponses pour récupérer les occurences par an et par années
            foreach ($responses as $response) {
                //Je converti en date pour récupérer les valeurs
                $dueDate = new DateTime($response->due_date);
                $year = $dueDate->format('Y');
                $month = $dueDate->format('m');

                //Je vérifie si l'année et le mois actuelle sont déjà défini
                //Si ce n'est pas définis
                if (!isset($value[$year][$month])) {
                    //J'initialise l'occurence à 1
                    $value[$year][$month] = 1;
                } else {
                    //Sinon je rajoute 1 à l'occurence
                    $value[$year][$month]++;
                }
            }
            
            // je trie par année et par mois
            ksort($value);
            foreach ($value as &$months) {
                ksort($months);
            }
            
          
            
        }
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
    public function getPipeCommercial($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"opportunities?status=ongoing");
        foreach($responses as $response){
            $value[]=[ $response->pipe_name, $response->user_name,$response->probability];
            
        }
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
    //!!! la réponse renvoie un message même si de base la response est 404-> date pas prise en compte
    public function getDepenses($idInPath,$request, $firstYear, $secondYear, $firstMonth, $secondMonth, $firstDay, $secondDay){
        $value = [];

        $endpoint =  'expenses?orderby=last_update_date?date_before=' . $secondDay . '%2F' . $secondMonth . '%2F' . $secondYear . '&date_after=' . $firstDay . '%2F' . $firstMonth . '%2F' . $firstYear."&is_paid=true";

        $responses = $this->getApiRelation($idInPath, $request,$endpoint);
        foreach($responses as $response){
            $value[]=$response;
                        
        }
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
    public function getStock($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"products");
        foreach($responses as $response){
            $value[]= [$response->name,$response->stock];
            
        }
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
    public function getNoteFrais($idInPath,$request){
        $value = [];

        $responses = $this->getApiRelation($idInPath, $request,"expenses");
        foreach($responses as $response){
            if($response->workforce_id !== null){
                
                $value[]=[$response->title,$response->total_amount];
            }
            
            
        }
        
        
        $responses = new Response();
        $responses->setContent(json_encode($value));
        return $responses;
    }
}
