<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
final class TeacherDashboardController extends AbstractController
{
    #[Route('/teacher/dashboard', name: 'app_teacher_dashboard')]
    public function index(
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        ResourceRepository $resourceRepository
    ): Response {
        $teacher = $this->getUser();
        
        // 1. Ses cours
        $courses = $courseRepository->findBy(['teacher' => $teacher], ['createdAt' => 'DESC'], 5);
        $courseCount = $courseRepository->count(['teacher' => $teacher]);

        // 2. Nombre total d'étudiants uniques inscrits à ses cours
        $studentCount = $enrollmentRepository->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.user)')
            ->join('e.course', 'c')
            ->where('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérer ses cours
        $courses = $courseRepository->findBy(['teacher' => $teacher], ['createdAt' => 'DESC']);
        
        // Statistiques rapides
        $stats = [
            'total_courses' => count($courses),
            'total_modules' => array_sum(array_map(fn($c) => $c->getModules()->count(), $courses)),
            'total_students' => array_sum(array_map(fn($c) => $c->getEnrollments()->count(), $courses)),
        ];

        return $this->render('teacher_dashboard/index.html.twig', [
            'courses' => $courses,
            'stats' => $stats,
        ]);
    }

    #[Route('/teacher/course/{id}/students', name: 'app_teacher_course_students')]
    public function courseStudents(Course $course): Response
    {
        // Vérifier que c'est bien le cours de ce prof
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas le professeur de ce cours.");
        }

        return $this->render('teacher_dashboard/students.html.twig', [
            'course' => $course,
            'enrollments' => $course->getEnrollments(),
        ]);
    }
}
