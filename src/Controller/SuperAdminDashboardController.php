<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\ModuleRepository;
use App\Repository\ResourceRepository;
use App\Repository\EnrollmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
final class SuperAdminDashboardController extends AbstractController
{
    #[Route('/super/admin/dashboard', name: 'app_super_admin_dashboard')]
    public function index(
        UserRepository $userRepo,
        CourseRepository $courseRepo,
        ModuleRepository $moduleRepo,
        ResourceRepository $resourceRepo,
        EnrollmentRepository $enrollmentRepo
    ): Response
    {
        // Statistics for Chairman
        $stats = [
            'total_users' => count($userRepo->findAll()),
            'total_admins' => count($userRepo->findByRole('ROLE_ADMIN')),
            'total_teachers' => count($userRepo->findByRole('ROLE_TEACHER')),
            'total_students' => count($userRepo->findByRole('ROLE_STUDENT')),
            'total_courses' => count($courseRepo->findAll()),
            'total_modules' => count($moduleRepo->findAll()),
            'total_resources' => count($resourceRepo->findAll()),
            'total_enrollments' => count($enrollmentRepo->findAll()),
        ];

        // Recent enrollments
        $recentEnrollments = $enrollmentRepo->findBy([], ['enrolledAt' => 'DESC'], 5);

        // Data for charts
        $userDistribution = [
            'teachers' => $stats['total_teachers'],
            'students' => $stats['total_students'],
            'admins' => $stats['total_admins'],
        ];

        // Popular courses
        $popularCourses = $enrollmentRepo->findPopularCourses(5);

        return $this->render('super_admin_dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_enrollments' => $recentEnrollments,
            'user_distribution' => $userDistribution,
            'popular_courses' => $popularCourses,
        ]);
    }
}
