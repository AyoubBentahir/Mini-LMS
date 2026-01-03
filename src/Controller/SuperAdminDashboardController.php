<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SuperAdminDashboardController extends AbstractController
{
    #[Route('/super/admin/dashboard', name: 'app_super_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('super_admin_dashboard/index.html.twig', [
            'controller_name' => 'SuperAdminDashboardController',
        ]);
    }
}
