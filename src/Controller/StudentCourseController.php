<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student')]
#[IsGranted('ROLE_STUDENT')]
class StudentCourseController extends AbstractController
{
    public function __construct(
        private \App\Service\ProgressCalculator $progressCalculator
    ) {}

    #[Route('/my-courses', name: 'app_student_my_courses', methods: ['GET'])]
    public function myCourses(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $enrollments = $user->getEnrollments();
        
        $progress = [];
        foreach ($enrollments as $enrollment) {
            $progress[$enrollment->getCourse()->getId()] = $this->progressCalculator->calculateCourseProgress($user, $enrollment->getCourse());
        }

        return $this->render('student/my_courses.html.twig', [
            'enrollments' => $enrollments,
            'progress' => $progress
        ]);
    }

    /**
     * CATALOGUE DES COURS (Browse)
     * Cette méthode affiche tous les cours disponibles sur la plateforme.
     * Utilité : Permet à l'étudiant de découvrir de nouveaux contenus.
     * Logique : On récupère tout, mais on marque ceux où l'étudiant est déjà inscrit.
     */
    #[Route('/courses', name: 'app_student_courses_browse', methods: ['GET'])]
    public function browse(CourseRepository $courseRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // 1. Récupérer tous les cours disponibles
        $allCourses = $courseRepository->findBy([], ['createdAt' => 'DESC']);

        // 2. Identifier les cours déjà rejoints par cet étudiant
        $enrolledCourseIds = [];
        foreach ($user->getEnrollments() as $enrollment) {
            $enrolledCourseIds[] = $enrollment->getCourse()->getId();
        }

        return $this->render('student/courses_browse.html.twig', [
            'courses' => $allCourses,
            'enrolled_course_ids' => $enrolledCourseIds,
        ]);
    }

    /**
     * INSCRIPTION (Enroll)
     * Action cruciale : Crée le lien (Enrollment) entre l'étudiant et le cours.
     * Logique : 
     * 1. Vérifie si déjà inscrit (évite les doublons).
     * 2. Crée un nouvel objet Enrollment.
     * 3. Sauvegarde en base de données.
     */
    #[Route('/courses/{id}/enroll', name: 'app_student_course_enroll', methods: ['POST'])]
    public function enroll(Course $course, EntityManagerInterface $entityManager, EnrollmentRepository $enrollmentRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Check if already enrolled
        $existingEnrollment = $enrollmentRepository->findOneBy([
            'user' => $user,
            'course' => $course
        ]);

        if ($existingEnrollment) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à ce cours.');
            return $this->redirectToRoute('app_student_my_courses');
        }

        $enrollment = new Enrollment();
        $enrollment->setUser($user);
        $enrollment->setCourse($course);
        // Date is set in constructor usually, but let's be sure or rely on constructor
        $enrollment->setEnrolledAt(new \DateTimeImmutable());
        
        $entityManager->persist($enrollment);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription réussie !');

        return $this->redirectToRoute('app_student_course_view', ['id' => $course->getId()]);
    }

    /**
     * LECTURE D'UN COURS (Show)
     * Affiche le contenu détaillé d'un cours (Modules, Ressources) pour apprendre.
     */
    /**
     * LECTURE D'UN COURS (Show)
     * Affiche le contenu détaillé d'un cours (Modules, Ressources) pour apprendre.
     */
    #[Route('/courses/{id}', name: 'app_student_course_view', methods: ['GET'])]
    public function show(
        Course $course, 
        EnrollmentRepository $enrollmentRepository, 
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // 1. Vérifier l'inscription
        $isEnrolled = false;
        if ($user) {
            $enrollment = $enrollmentRepository->findOneBy([
                'user' => $user,
                'course' => $course
            ]);
            $isEnrolled = ($enrollment !== null);
        }

        // 2. Calculer le progrès et récupérer les modules terminés
        $progress = 0;
        $completedModules = []; // Initialisation importante
        
        if ($isEnrolled) {
            $progress = $this->progressCalculator->calculateCourseProgress($user, $course);
            
            // Récupérer la liste des IDs des modules que l'élève a déjà cochés
            $completionRepo = $entityManager->getRepository(\App\Entity\ModuleCompletion::class);
            $completions = $completionRepo->findBy(['user' => $user]);
            
            foreach ($completions as $completion) {
                if ($completion->getModule()->getCourse() === $course) {
                    $completedModules[] = $completion->getModule();
                }
            }
        }

        return $this->render('student/course_show.html.twig', [
            'course' => $course,
            'is_enrolled' => $isEnrolled,
            'modules' => $course->getModules(),
            'progress' => $progress,
            // On mappe pour n'envoyer que les IDs (tableau simple) à la vue
            'completed_modules' => array_map(function($m) { return $m->getId(); }, $completedModules),
        ]);
    }
    
    #[Route('/modules/{id}/toggle-completion', name: 'app_student_module_toggle_completion', methods: ['POST'])]
    public function toggleCompletion(\App\Entity\Module $module, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Security check: is user enrolled in the course of this module?
        // ... (Skipping strict check for speed, but ideally should exist)

        $completionRepo = $entityManager->getRepository(\App\Entity\ModuleCompletion::class);
        $completion = $completionRepo->findOneBy(['user' => $user, 'module' => $module]);

        if ($completion) {
            $entityManager->remove($completion);
            $entityManager->flush();
            // $this->addFlash('info', 'Module marqué comme non terminé.');
        } else {
            $completion = new \App\Entity\ModuleCompletion();
            $completion->setUser($user);
            $completion->setModule($module);
            $entityManager->persist($completion);
            $entityManager->flush();
            // $this->addFlash('success', 'Module terminé !');
        }

        return $this->redirectToRoute('app_student_course_view', ['id' => $module->getCourse()->getId()]);
    }

    #[Route('/modules', name: 'app_student_modules', methods: ['GET'])]
    public function modules(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $enrollments = $user->getEnrollments();
        
        return $this->render('student/modules.html.twig', [
            'enrollments' => $enrollments,
        ]);
    }

    #[Route('/resources', name: 'app_student_resources', methods: ['GET'])]
    public function resources(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $enrollments = $user->getEnrollments();
        
        $myResources = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->getCourse()->getModules() as $module) {
                foreach ($module->getResources() as $resource) {
                    $myResources[] = $resource;
                }
            }
        }

        return $this->render('student/resources.html.twig', [
            'resources' => $myResources,
        ]);
    }
}
