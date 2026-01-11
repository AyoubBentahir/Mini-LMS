<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\EnrollmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepo, EnrollmentRepository $enrollmentRepo): Response
    {
        // Get all teachers and students
        $teachers = $userRepo->findByRole('ROLE_TEACHER');
        $students = $userRepo->findByRole('ROLE_STUDENT');

        // Count active/inactive
        $activeTeachers = array_filter($teachers, fn($u) => $u->isIsActive());
        $inactiveTeachers = array_filter($teachers, fn($u) => !$u->isIsActive());
        $activeStudents = array_filter($students, fn($u) => $u->isIsActive());
        $inactiveStudents = array_filter($students, fn($u) => !$u->isIsActive());

        // Recent student enrollments
        $recentEnrollments = $enrollmentRepo->findBy([], ['enrolledAt' => 'DESC'], 5);

        $stats = [
            'total_teachers' => count($teachers),
            'active_teachers' => count($activeTeachers),
            'inactive_teachers' => count($inactiveTeachers),
            'total_students' => count($students),
            'active_students' => count($activeStudents),
            'inactive_students' => count($inactiveStudents),
        ];

        return $this->render('admin_dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_enrollments' => $recentEnrollments,
        ]);
    }
}
