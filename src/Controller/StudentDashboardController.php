<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CONTROLEUR TABLEAU DE BORD ÉTUDIANT
 * 
 * Rôle : C'est la "Page d'Accueil" de l'étudiant.
 * Ce contrôleur ne gère PAS les cours en detail, mais donne une vue d'ensemble.
 * Il calcule les stats rapides et affiche le bouton "Reprendre le cours".
 */
final class StudentDashboardController extends AbstractController
{
    public function __construct(
        private \App\Service\ProgressCalculator $progressCalculator
    ) {}

    /**
     * VUE D'ENSEMBLE (Dashboard)
     * Affiche : 
     * 1. Le nombre total de cours rejoints.
     * 2. Les inscriptions récentes.
     * 3. Un module "Reprenons là où vous en étiez" (Smart Resume).
     */
    /**
     * VUE D'ENSEMBLE (Dashboard)
     * Affiche : 
     * 1. Le nombre total de cours rejoints.
     * 2. Les inscriptions récentes.
     * 3. Un module "Reprenons là où vous en étiez" (Smart Resume).
     */
    #[Route('/student', name: 'app_student_home')] 
    #[Route('/student/dashboard', name: 'app_student_dashboard')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Toutes les inscriptions de l'élève
        $enrollments = $user->getEnrollments();
        
        // Calcul du nombre de modules terminés (Bonus stat)
        $completedModulesCount = $this->progressCalculator->getCompletedModulesCount($user);

        // --- LOGIQUE "SMART RESUME" (Reprendre le cours) ---
        // On cherche le premier cours qui n'est pas terminé (progrès < 100%).
        $resumeEnrollment = null;
        $resumeProgress = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->getCourse();
            $progress = $this->progressCalculator->calculateCourseProgress($user, $course);
            
            // On prend le premier cours non fini
            if ($progress < 100) {
                $resumeEnrollment = $enrollment;
                $resumeProgress = $progress;
                break;
            }
        }

        // Si tout est fini (ou rien commencé), on affiche le dernier cours rejoint par défaut
        if (!$resumeEnrollment && count($enrollments) > 0) {
            $resumeEnrollment = $enrollments->first();
             // ensure we have the progress for this fallback one
            if ($resumeEnrollment) {
                 $resumeProgress = $this->progressCalculator->calculateCourseProgress($user, $resumeEnrollment->getCourse());
            }
        }

        return $this->render('student_dashboard/index.html.twig', [
            'total_enrollments' => count($enrollments),
            'recent_enrollments' => $enrollments->slice(0, 3), 
            'completed_modules_count' => $completedModulesCount,
            'resume_enrollment' => $resumeEnrollment,
            'resume_progress' => $resumeProgress,
        ]);
    }
}
