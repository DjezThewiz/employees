<?php

namespace App\Repository;

use App\Entity\Mission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mission>
 *
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

   /**
    * @return Mission[] Returns an array of Mission objects
    */
   public function findUnfinishedMissionsByEmployee($employee): array
   {
        return $this->createQueryBuilder('miss')
           ->select('m')    // Facultatif
           ->from(Mission::class, 'm')  // Facultatif aussi parce que createQueryBuilder() prend les 2 lignes en charge
           ->innerJoin('m.employees', 'e')
           ->where('e.id = :id')
           ->andwhere('m.status != :status')    // <> DIFFERENT
           ->setParameters([
            'id' => $employee->getId(),
            'status' => 'done',
           ])
           ->getQuery()
           ->getResult()    // Si on attend un ou plusieurs résultats
        ;  
   }
}
