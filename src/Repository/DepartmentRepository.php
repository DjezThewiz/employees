<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Department>
 *
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    // RECHERCHE PAR MOTS CLEF ET TRI PAR ORDRE CROISSANT ET DÉCROISSANT
    public function findByKeywordAndSort($keyword, $sortBy, $sortOrder): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.deptName LIKE :keyword')
            ->setParameter('keyword', '%' .$keyword. '%')
            ->orderBy('d.' . $sortBy, $sortOrder)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    // RECHERCHE DU DÉPARTEMENT ACTUEL D'UN EMPLOYÉ
    public function findActualDepartmentForEmployee($employee): ?Department
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.deptEmps', 'de') // 'de' est l'allias de la propriété deptEmps (collection)
            ->innerJoin('de.employee', 'e') // 'e est l'allias de la propriété employee
            ->where('e.id = :id')
            ->andWhere('de.toDate = :toDate')
            ->setParameters([
                'id' => $employee->getId(),
                'toDate' => '9999-01-01',
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
