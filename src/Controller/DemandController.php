<?php

namespace App\Controller;

use App\Entity\Demand;
use App\Form\DemandType;
use App\Repository\DemandRepository;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/demand')]
class DemandController extends AbstractController
{
    #[Route('/', name: 'app_demand_index', methods: ['GET'])]
    public function index(DemandRepository $demandRepository, EmployeeRepository $emRep): Response
    {
        // Récupérer tous les employés avec leurs demandes associées
        $employees = $emRep->findEmployeesByDemands();      //dd($employees);

        return $this->render('demand/index.html.twig', [
            'demands' => $demandRepository->findAll(),
            'employees' => $employees,
        ]);
    }

    #[Route('/new', name: 'app_demand_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demand = new Demand();
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demand);
            $entityManager->flush();

            return $this->redirectToRoute('app_demand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demand/new.html.twig', [
            'demand' => $demand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demand_show', methods: ['GET'])]
    public function show(Demand $demand): Response
    {
        return $this->render('demand/show.html.twig', [
            'demand' => $demand,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demand $demand, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demand_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demand/edit.html.twig', [
            'demand' => $demand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demand_delete', methods: ['POST'])]
    public function delete(Request $request, Demand $demand, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demand->getId(), $request->request->get('_token'))) {
            $entityManager->remove($demand);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demand_index', [], Response::HTTP_SEE_OTHER);
    }

    // MIS À DES MISSIONS D'UN EMPLOYÉ
    #[Route('/{id}/update', name: 'update_demand', methods: ['POST'])]
    public function updateDemand(Request $request, Demand $demand, EntityManagerInterface $entityManager): Response
    {
        // Cette action est reservée à l'utilisateur connecté
        $admin = $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($admin) {
            // On vérifie si le formulaire a été soumis
            if ($request) {
                $action = $request->request->get('action');

                // On vérifie si l'action est soumise
                if ($action === 'accept') {
                    $this->acceptDemand($demand, $entityManager);
                } elseif ($action === 'refuse') {
                    $this->refuseDemand($demand, $entityManager);
                } else {
                    $this->addFlash('notFound', 'Aucune demande trouvée.');
                } 

                // Affichage d'une notification de succès après la mis à jour d'une mission
                $this->addFlash('success', 'La demande a été mise à jour avec succès.');
            }
        } else {
            $this->addFlash('error', 'Ce rôle est reservé à \'administrateur.');
        }

        // On récupère l'URL de la page actuelle et on se redirige vers la page en question
        return $this->redirect($request->headers->get('referer'));
    }

    // ACCEPTER LA DEMANDE
    private function acceptDemand(Demand $demand, EntityManagerInterface $entityManager): void
    {
        // Mis à jour du statut de la demande à 1 = 'demande acceptée'
        $demand->setStatus(1);
        $entityManager->flush();
    }

    // REFUSER LA DEMANDE
    private function refuseDemand(Demand $demand, EntityManagerInterface $entityManager): void
    {
        // Mis à jour du statut de la demande à 0 = 'demande refusée'
        $demand->setStatus(0);
        $entityManager->flush();

        $this->addFlash('error', 'L\'employé a déjà d\'autres missions en cours.');
    }
}
