<?php

namespace App\Controller;

use App\Service\AxaunoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashbordController extends AbstractController
{
    #[Route('api/{id}/dash/ca/{month}/{year}/{status}', name: 'app_ca_month')]
    public function caPerMonth(AxaunoteService $service, Request $request, $id, $month, $year,$status)
    {
        $response = $service->getCaPerMonth($id, $request,$month,$year,$status);
        return $response;
    }
    #[Route('api/{id}/dash/ca/{year}/{status}', name: 'app_ca_year')]
    public function caPerYear(AxaunoteService $service, Request $request, $id, $year)
    {
        $response = $service->getCaPerYear($id, $request,$year);
        return $response;
    }
    #[Route('api/{id}/dash/ca/{firstDay}/{firstMonth}/{firstYear}-{secondDay}/{secondMonth}/{secondYear}/{status}', name: 'app_ca_date')]
    public function caPerDate(AxaunoteService $service, Request $request, $id, $firstDay,$firstMonth,$firstYear,$secondDay,$secondMonth,$secondYear)
    {
        $response = $service->getCaPerDate($id,$request,$firstYear,$secondYear,$firstMonth,$secondMonth,$firstDay,$secondDay);
        return $response;
    }
    #[Route('api/{id}/dash/marge', name: 'app_marge')]
    public function margeProduct(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getMargeProduct($id,$request);
        return $response;
    }
    #[Route('api/{id}/dash/devis', name: 'app_devis')]
    public function devis(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getNumberInvoices($id,$request);
        return $response;
    }
    // #[Route('api/{id}/dash/opportunites/{firstDay}/{firstMonth}/{firstYear}-{secondDay}/{secondMonth}/{secondYear}', name: 'app_opportunites')]
    // public function opportunity(AxaunoteService $service, Request $request, $id,$firstDay,$firstMonth,$firstYear,$secondDay,$secondMonth,$secondYear)
    // {
    //     $response = $service->getNumberOpportunity($id,$request,$firstYear,$secondYear,$firstMonth,$secondMonth,$firstDay,$secondDay);
    //     return $response;
    // }
    #[Route('api/{id}/dash/opportunites/', name: 'app_opportunites')]
    public function opportunitys(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getNumberOpportunitys($id,$request);
        return $response;
    }
    #[Route('api/{id}/dash/pipe', name: 'app_pipe')]
    public function pipe(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getPipeCommercial($id,$request);
        return $response;
    }
    #[Route('api/{id}/dash/depense/{firstDay}/{firstMonth}/{firstYear}-{secondDay}/{secondMonth}/{secondYear}', name: 'app_depense')]
    public function depense(AxaunoteService $service, Request $request, $id,$firstDay,$firstMonth,$firstYear,$secondDay,$secondMonth,$secondYear)
    {
        $response = $service->getDepenses($id,$request,$firstYear,$secondYear,$firstMonth,$secondMonth,$firstDay,$secondDay);
        return $response;
    }
    #[Route('api/{id}/dash/stock', name: 'app_stock')]
    public function stock(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getStock($id,$request);
        return $response;
    }
    #[Route('api/{id}/dash/note', name: 'app_note')]
    public function note(AxaunoteService $service, Request $request, $id)
    {
        $response = $service->getNoteFrais($id,$request);
        return $response;
    }
}
