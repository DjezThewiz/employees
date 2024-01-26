<?php

namespace App\Repository;

use App\Entity\Title;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Title>
 *
 * @method Title|null find($id, $lockMode = null, $lockVersion = null)
 * @method Title|null findOneBy(array $criteria, array $orderBy = null)
 * @method Title[]    findAll()
 * @method Title[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Title::class);
    }

    // RECHERCHE DE TITRES D'UN DÉPARTEMENT
    public function findTitlesByDepartment($department): ?array
    {   // dd("OK");
        return $this->createQueryBuilder('t')
            ->innerJoin('t.departments', 'd')
            ->where('d.id = :id')
            ->setParameter('id', $department->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}
