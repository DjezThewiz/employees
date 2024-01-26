<?php

namespace App\Controller;

use App\Entity\Department;
use App\Entity\DeptManager;
use App\Form\DepartmentType;
use App\Form\SearchDepartmentType;
use App\Repository\DepartmentRepository;
use App\Repository\EmployeeRepository;
use App\Repository\DeptManagerRepository;
use App\Repository\TitleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;

#[Route('/department')]
class DepartmentController extends AbstractController
{
    // FILTRE (RECHERCHE, TRI) ET PAGINATION DES DÉPARTEMENTS
    #[Route('/', name: 'app_department_index', methods: ['GET', 'POST'])]
    public function index(DepartmentRepository $departmentRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $form = $this->createForm(SearchDepartmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { //dump($form->isValid());die;
            $data = $form->getData();   //dump($data);die;
            $searchKeyword = $data['q'];    //Mot clé recherché
            $sortBy = $data['sortBy'] ?? 'deptName'; // Tri par défaut par le prénom
            $sortOrder = $data['sortOrder'] ?? 'asc'; // Ordre de tri par défaut ascendant
            
            $departments = $departmentRepository->findByKeywordAndSort($searchKeyword, $sortBy, $sortOrder);
        } else {
            $departments = $departmentRepository->findAll(); 
        }
    
        // Pagination des résultasts grâce au "paginator"
        $pagination = $paginator->paginate(
            $departments,
            $request->query->getInt('page', 1),
            5   //Nombre limite des départements par page
        );

        return $this->render('department/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_department_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $department = new Department();
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($department);
            $entityManager->flush();

            return $this->redirectToRoute('app_department_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('department/new.html.twig', [
            'department' => $department,
            'form' => $form,
        ]);
    }

    // SÉLECTION DES EMPLOYÉS, DES MANAGERS AINSI QUE DES DÉPARTEMENTS
    #[Route('/{id}', name: 'app_department_show', methods: ['GET'])]
    public function show(Department $department, EmployeeRepository $emRepo, DeptManagerRepository $dmRepo, TitleRepository $titleRepo): Response
    {
        $department->actualEmployees = $emRepo->findActualEmployeesByDepartment($department);
        $managers = $dmRepo->findManagersByDepartment($department);
        $titles = $titleRepo->findTitlesByDepartment($department);

        return $this->render('department/show.html.twig', [
            'department' => $department,
            'managers' => $managers,
            'titles' => $titles,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_department_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_department_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('department/edit.html.twig', [
            'department' => $department,
            'form' => $form,
        ]);
    }
 
    // SUPPRESSION DU DÉPARTEMENT DÉPOUVU D'EMPLOYÉS
    #[Route('/{id}', name: 'app_department_delete', methods: ['POST'])]
    public function delete(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        // On ne supprime que le département qui n'a pas d'employés
        if ($department->getEmployees()->count() == 0) {
            if ($this->isCsrfTokenValid('delete'.$department->getId(), $request->request->get('_token'))) {
                $entityManager->remove($department);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('notice', 'Ce département ne peut pas être supprimé!');

            // On retourne sur la même page.
            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirectToRoute('app_department_index', [], Response::HTTP_SEE_OTHER);
    }

    // NOMMER UN MANAGER
    #[Route('/{id}/name_manage', name: 'name_manager', methods: ['GET', 'POST'])]
    public function updateManager(Department $department, EntityManagerInterface $entityManager, EmployeeRepository $empRepo, Request $request): Response
    {
        $employeeId = $request->request->get('employee_id');    // dd($employeeId);

        if ($employeeId) {
            // On récupère l'instance de l'entité Employee à partir de son id
            $selectedEmployee = $empRepo->find($employeeId);
    
            if ($selectedEmployee) {
                // On crée une nouvelle instance de DeptManager
                $deptManager = new DeptManager();
    
                // On définit l'employé, le département et les dates dans le DeptManager
                $deptManager->setManager($selectedEmployee);
    
                // On définit la date de début à aujourd'hui
                $deptManager->setFromDate(new \DateTime());
    
                // On définit la date de fin à 1 an plus tard
                $endDate = new \DateTime();
                $endDate->add(new \DateInterval('P1Y'));
                $deptManager->setToDate($endDate);
    
                // Ajout du DeptManager à la collection du département
                $department->addDeptManager($deptManager);
    
                // Persistence des changements dans la base de données
                $entityManager->persist($department);
                $entityManager->flush();
            }
        }

        return $this->render('department/name_manager.html.twig', [
            'department' => $department,
        ]);
    }

    // STATISTIQUES & INFOS DU DÉPARTEMENT
    #[Route('/{id}/stat_infos', name: 'app_department_stat_infos', methods: ['GET'])]
    public function statInfos(Department $department, EmployeeRepository $emRepo, DeptManagerRepository $dmRepo): Response
    {
        $managers = $dmRepo->findManagersByDepartment($department);     // dd($managers);
        $employees = $emRepo->findEmployeesByDepartment($department);

        // Conversion de tableau en collection
        $employeesCollection = new ArrayCollection($emRepo->findEmployeesByDepartment($department));

        // Obtension de nombre total d'employés
        $nbreEmployees = $employeesCollection->count();      // dd($nbreEmployees);

        // Salaire mpoyen de tous les employés excepté le celui du manager
        $avgSalary = $emRepo->findAverageSalaryForEmployeesByDepartment($department);   // dd($avgSalary);

        return $this->render('department/stat_infos.html.twig', [
            'managers' => $managers,
            'employees' => $employees,
            'nbreEmployees' => $nbreEmployees,
            'avgSalary' => $avgSalary,
        ]);
    }
}
