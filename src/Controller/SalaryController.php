<?php

namespace App\Controller;

use App\Entity\Salary;
use App\Form\SalaryType;
use App\Repository\SalaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/salary')]
class SalaryController extends AbstractController
{
    #[Route('/', name: 'app_salary_index', methods: ['GET'])]
    public function index(SalaryRepository $salaryRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $salaryRepository->createQueryBuilder('s')->getQuery();
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('salary/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_salary_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $salary = new Salary();
        $form = $this->createForm(SalaryType::class, $salary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($salary);
            $entityManager->flush();

            return $this->redirectToRoute('app_salary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('salary/new.html.twig', [
            'salary' => $salary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_salary_show', methods: ['GET'])]
    public function show(Salary $salary): Response
    {
        return $this->render('salary/show.html.twig', [
            'salary' => $salary,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_salary_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Salary $salary, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SalaryType::class, $salary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_salary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('salary/edit.html.twig', [
            'salary' => $salary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_salary_delete', methods: ['POST'])]
    public function delete(Request $request, Salary $salary, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$salary->getId(), $request->request->get('_token'))) {
            $entityManager->remove($salary);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_salary_index', [], Response::HTTP_SEE_OTHER);
    }
}
