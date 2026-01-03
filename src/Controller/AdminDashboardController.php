<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminDashboardController extends AbstractController
{
    public function __construct(
        private \App\Repository\UserRepository $userRepository,
        private \App\Repository\CourseRepository $courseRepository
    ) {}

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        // 1. Récupération des Statistiques
        $totalUsers = $this->userRepository->count([]);
        $totalStudents = $this->userRepository->countStudents(); // Méthode à créer dans UserRepository
        $totalTeachers = $this->userRepository->countTeachers(); // Méthode à créer dans UserRepository
        $totalCourses = $this->courseRepository->count([]);

        // 2. Derniers inscrits
        $latestUsers = $this->userRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin_dashboard/index.html.twig', [
            'stats' => [
                'users' => $totalUsers,
                'students' => $totalStudents,
                'teachers' => $totalTeachers,
                'courses' => $totalCourses,
            ],
            'latest_users' => $latestUsers,
        ]);
    }
}
