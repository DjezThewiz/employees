<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    // PAGINATION & RECHERCHE PAR MOT CLÉ
    public function findByKeywordAndSort($keyword, $sortBy, $sortOrder)
    {
            return $this->createQueryBuilder('e')
            ->where('e.firstName LIKE :keyword OR e.lastName LIKE :keyword')
            ->setParameter('keyword', '%'.$keyword.'%')
            ->setMaxResults(3)
            ->orderBy('e.' . $sortBy, $sortOrder)
            ->getQuery()
            ->getResult();
    }

    // RECHERCHE DES EMPLOYÉS ACTUELS D'UN DÉPARTEMENT
    public function findActualEmployeesByDepartment($department): array
    {
        $results = $this->createQueryBuilder('emp')
            ->select('e')    // Sélectionne l'employé
            ->from(Employee::class, 'e')
            ->innerJoin('e.deptEmps', 'de')
            ->innerJoin('de.department', 'd')
            ->where('d.id = :id')
            ->andWhere('de.toDate = :toDate')    // 'toDate' se trouve dans la table dept_emp (de)
            ->setParameters([
                'id' => $department->getId(),
                'toDate' => '9999-01-01',
            ])
            ->getQuery()
            ->getResult();
    
        return $results;
    }  
    
    // RECHERCHE DES EMPLOYÉS D'UN DÉPARTEMENT
    public function findEmployeesByDepartment($department): array
    {
        $queryBuilder = $this->createQueryBuilder('e')
        ->innerJoin('e.department', 'd')
        ->where('d.id = :id')
        ->setParameter('id', $department->getId())
        ->getQuery();

        $result = $queryBuilder->getResult();   // dd($result);

        return $result;
    }

    // RECHERCHE DE SALAIRE MOYEN EMPLOYÉS D'UN DÉPARTEMENT
    public function findAverageSalaryForEmployeesByDepartment($department): float
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('AVG(s.salary) as averageSalary')
            ->leftJoin('e.salaries', 's')
            ->leftJoin('e.deptManagers', 'dm') // Tous les employés seront inclus dans le résultat, même s'ils n'ont pas de manager.
            ->innerJoin('e.department', 'd')
            ->where('d.id = :id')
            ->andWhere('dm.id IS NULL') // Exclure les employés qui sont aussi managers
            ->setParameter('id', $department->getId())
            ->getQuery();

        $result = $queryBuilder->getSingleScalarResult();

        return (float) $result;
    }

    // RECHERCHE DES EMPLOYÉS QUI ONT FAIT DES DEMANDES
    public function findEmployeesByDemands(): array
    {
        return $this->createQueryBuilder('e')
        ->innerJoin('e.demands', 'd')
        ->addSelect('d') // Pour sélectionner les demandes avec les employés
        ->where('d.status IN (:status)')
        ->setParameter('status', [null, 0, 1])
        ->getQuery()
        ->getResult();
    }
}
