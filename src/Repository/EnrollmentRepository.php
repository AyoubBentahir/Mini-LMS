<?php

namespace App\Repository;

use App\Entity\Enrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrollment>
 */
class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    /**
     * Find most popular courses by enrollment count
     */
    public function findPopularCourses(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->select('c.title, COUNT(e.id) as enrollment_count')
            ->join('e.course', 'c')
            ->groupBy('c.id')
            ->orderBy('enrollment_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
