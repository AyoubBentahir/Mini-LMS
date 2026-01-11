<?php

namespace App\Repository;

use App\Entity\ModuleCompletion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModuleCompletion>
 *
 * @method ModuleCompletion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuleCompletion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuleCompletion[]    findAll()
 * @method ModuleCompletion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleCompletionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleCompletion::class);
    }
}
