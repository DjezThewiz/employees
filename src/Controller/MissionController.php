<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Employee;
use App\Form\MissionType;
use App\Repository\MissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/mission')]
class MissionController extends AbstractController
{
    #[Route('/', name: 'app_mission_index', methods: ['GET'])]
    public function index(MissionRepository $missionRepository): Response
    {
        return $this->render('mission/index.html.twig', [
            'missions' => $missionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mission_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mission = new Mission();
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('app_mission_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mission_show', methods: ['GET'])]
    public function show(Mission $mission): Response
    {
        return $this->render('mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mission_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mission_delete', methods: ['POST'])]
    public function delete(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            $entityManager->remove($mission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mission_index', [], Response::HTTP_SEE_OTHER);
    }

    // LES MISSIONS D'UN EMPLOYÉ
    #[Route('/employee/{id}', name: 'app_mission_mes_missions_show', methods: ['GET'])]
    public function show_mission(Employee $employee, MissionRepository $missRepo, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        // On empêche l'utilisateur non connecté d'avoir accès à cette action, même s'il passe par URL.
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupérez les missions
        $missions = $missRepo->findUnfinishedMissionsByEmployee($employee);
        
        // Renvoyez la réponse avec les missions
        return $this->render('employee/show_mes_missions.html.twig', [
            'missions' => $missions,
        ]);
    }

    // MIS À DES MISSIONS D'UN EMPLOYÉ
    #[Route('/mission/{id}/update', name: 'update_mission', methods: ['POST'])]
    public function updateMission(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        // On vérifie si le formulaire a été soumis
        if ($request) {
            $action = $request->request->get('action');

            // On vérifie si l'action est soumise
            if ($action === 'finish') {
                $this->finishMission($mission, $entityManager);
            } elseif ($action === 'accept') {
                $this->acceptMission($mission, $entityManager);
            } else {
                $this->addFlash('notFound', 'Aucune mission trouvée.');
            } 

            // Si la mission n'est plus en cours
            if ($mission->getStatus() != 'ongoing') {
                $this->addFlash('success', 'La mission a été mise à jour avec succès.');
            }
            
            // On récupère l'URL de la page actuelle et on se redirige vers la page en question
            return $this->redirect($request->headers->get('referer'));
        }  
    }

    // TERMINER LA MISSION
    private function finishMission(Mission $mission, EntityManagerInterface $entityManager): void
    {
        // Mis à jour du statut de la mission à 'done = terminée'
        $mission->setStatus('done');
        $entityManager->flush();
    }

    // ACCEPTER LA MISSION
    private function acceptMission(Mission $mission, EntityManagerInterface $entityManager): void
    {
        // Mis à jour du statut de la mission à 'ongoing = en cours'
        if ($mission->getStatus() != 'ongoing') {
            $mission->setStatus('ongoing');
            $entityManager->flush();
        } else {
            $this->addFlash('error', 'L\'employé a déjà d\'autres missions en cours.');
        }
    }

    /*
    private function deleteMission(Mission $mission, EntityManagerInterface $entityManager): void
    {
        // Suppression de la mission
        $entityManager->remove($mission);
        $entityManager->flush();
    }
    */
}       
