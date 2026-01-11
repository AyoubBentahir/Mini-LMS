<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        // 3. Nombre total de ressources dans ses cours
        $resourceCount = $resourceRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.module', 'm')
            ->join('m.course', 'c')
            ->where('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('teacher_dashboard/index.html.twig', [
            'courses' => $courses,
            'courseCount' => $courseCount,
            'studentCount' => $studentCount,
            'resourceCount' => $resourceCount,
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
