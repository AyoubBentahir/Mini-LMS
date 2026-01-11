<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\ModuleCompletionRepository;

class ProgressCalculator
{
    public function __construct(
        private ModuleCompletionRepository $completionRepository
    ) {}

    public function calculateCourseProgress(User $user, Course $course): int
    {
        $modules = $course->getModules();
        $totalModules = count($modules);

        if ($totalModules === 0) {
            return 0; // Or 100? No, 0 content = 0 progress usually.
        }

        $completedCount = 0;
        foreach ($modules as $module) {
            $isCompleted = $this->completionRepository->findOneBy([
                'user' => $user,
                'module' => $module
            ]);
            
            if ($isCompleted) {
                $completedCount++;
            }
        }

        return (int) round(($completedCount / $totalModules) * 100);
    }
    
    public function getCompletedModulesCount(User $user): int
    {
        return $this->completionRepository->count(['user' => $user]);
    }
}
