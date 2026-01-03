<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentDashboardController extends AbstractController
{
    #[Route('/student/dashboard', name: 'app_student_dashboard')]
    public function index(): Response
    {
        return $this->render('student_dashboard/index.html.twig', [
            'controller_name' => 'StudentDashboardController',
        ]);
    }
}
