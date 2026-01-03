<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private \App\Repository\CourseRepository $courseRepository,
        private \App\Repository\UserRepository $userRepository,
        // private \App\Repository\ResourceRepository $resourceRepository // Si besoin de compter les ressources globales
    ) {}

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Init stats
        $courses = [];
        $studentCount = 0;
        $resourceCount = 0;
        
        // 1. Logic ADMIN / SUPER ADMIN : Stats Globales
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SUPER_ADMIN')) {
             $courses = $this->courseRepository->findAll(); // On pourrait limiter à 5 ou ne pas afficher la liste complète
             // Pour l'admin, on affiche le TOTAL
             $studentCount = $this->userRepository->countStudents();
             // Pour les ressources, si on veut le total, il faudrait un ResourceRepository, ou via Course
             // Simplification : On compte les ressources via les cours récupérés
             foreach ($courses as $course) {
                foreach ($course->getModules() as $module) {
                    $resourceCount += $module->getResources()->count();
                }
            }
        }
        // 2. Logic TEACHER : Stats Personnelles
        elseif (in_array('ROLE_TEACHER', $user->getRoles())) {
            $courses = $user->getCourses();
            
            foreach ($courses as $course) {
                foreach ($course->getModules() as $module) {
                    $resourceCount += $module->getResources()->count();
                }
            }
            
            $students = [];
            foreach ($courses as $course) {
                foreach ($course->getEnrollments() as $enrollment) {
                    $students[$enrollment->getStudent()->getId()] = true;
                }
            }
            $studentCount = count($students);
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'courses' => $courses,
            'courseCount' => count($courses),
            'studentCount' => $studentCount,
            'resourceCount' => $resourceCount,
        ]);
    }
}
