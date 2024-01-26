<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Department;
use App\Form\EmployeeType;
use App\Form\SearchEmployeeType;
use App\Repository\EmployeeRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/employee')]
class EmployeeController extends AbstractController
{
    // FILTRE (RECHERCHE, TRI) ET PAGINATION DES EMPLOYÉS
    #[Route('/', name: 'app_employee_index', methods: ['GET', 'POST'])]
    public function index(EmployeeRepository $employeeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $form = $this->createForm(SearchEmployeeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { //dump($form->isValid());die;
            $data = $form->getData();   //dump($data);die;
            $searchKeyword = $data['q'];    //Mot clé recherché
            $sortBy = $data['sortBy'] ?? 'firstName'; // Tri par défaut par le prénom
            $sortOrder = $data['sortOrder'] ?? 'asc'; // Ordre de tri par défaut ascendant
            
            $employees = $employeeRepository->findByKeywordAndSort($searchKeyword, $sortBy, $sortOrder);
        } else {
            $employees = $employeeRepository->findAll(); 
        }
    
        // Pagination des résultasts grâce au "paginator"
        $pagination = $paginator->paginate(
            $employees,
            $request->query->getInt('page', 1),
            3   //Nombre limite des employés par page
        );

        return $this->render('employee/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_employee_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employee);
            $entityManager->flush();

            return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('employee/new.html.twig', [
            'employee' => $employee,
            'form' => $form,
        ]);
    }

    // PAGE PROFIL D'UN EMPLOYÉ
    #[Route('/{id}', name: 'app_employee_profile', methods: ['GET'])]
    public function profile(Employee $employee, DepartmentRepository $deptRepository): Response
    {   
        // actualDepartment est la propriété qu'on vient de créer pour faciliter le filtre
        $employee->actualDepartment = $deptRepository->findActualDepartmentForEmployee($employee);

        return $this->render('employee/profile.html.twig', [
            'employee' => $employee,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employee_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employee $employee, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('employee/edit.html.twig', [
            'employee' => $employee,
            'form' => $form,
        ]);
    }
    
    #[Route('/{id}', name: 'app_employee_delete', methods: ['POST'])]
    public function delete(Request $request, Employee $employee, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employee->getId(), $request->request->get('_token'))) {
            $entityManager->remove($employee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
    }

    // DOCUMENTS À TÉLÉCHARGER
    #[Route('/profile/{id}/download/documents', name: 'employee_documents', methods: ['GET'])]
    public function documents(Employee $employee): Response
    {
        return $this->render('employee/documents.html.twig', [
            'employee' => $employee,
        ]);
    }

    // TÉLÉCHARGER LE DOCUMENT
    #[Route('/profile/{id}/download/{document}', name: 'employee_download_document', methods: ['GET'])]
    public function downloadDocument(Employee $employee, string $document): Response
    {
         // On vérifie si le document demandé existe pour cet employé
        $filePath = null;

        if ($document === 'contract' && $employee->getContract()) {
            $filePath = $this->getParameter('contract_directory') . '/' . $employee->getContract();
        } elseif ($document === 'diploma' && $employee->getDiploma()) {
            $filePath = $this->getParameter('diploma_directory') . '/' . $employee->getDiploma();
        }

        if (!$filePath || !file_exists($filePath)) {
            return $this->render('employee/documents.html.twig', [
                'message' => 'Le document demandé n\'existe pas.',
                'employee' => $employee,
            ]);
        }

        return $this->render('employee/documents.html.twig', [
            'employee' => $employee,
        ]);
    }
}
