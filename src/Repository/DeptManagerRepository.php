<?php

namespace App\Repository;

use App\Entity\DeptManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeptManager>
 *
 * @method DeptManager|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeptManager|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeptManager[]    findAll()
 * @method DeptManager[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeptManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeptManager::class);
    }

    /**
    * @return DeptManager[] Returns an array of DeptManager objects
    */
    public function findManagersByDepartment($department): array
    { // dd('OK');
        $queryBuilder = $this->createQueryBuilder('dman')
            ->select('dm')
            ->from(DeptManager::class, 'dm')
            ->innerJoin('dm.manager', 'm')
            ->innerJoin('m.department', 'd')
            ->where('d.id = :id')
            ->setParameter('id', $department->getId())
            ->getQuery();

        $result = $queryBuilder->getResult();   // dd($result);

        return $result;
    }
}
