<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentCatalogController extends AbstractController
{
    #[Route('/student/catalog', name: 'app_student_catalog')]
    public function index(): Response
    {
        return $this->render('student_catalog/index.html.twig', [
            'controller_name' => 'StudentCatalogController',
        ]);
    }
}
