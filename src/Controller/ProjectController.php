<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\CreateProjectType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="app_project")
     */
    public function index(): Response
    {
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    /**
     * @Route("/project/create", name="app_create_project")
     */
    public function createProject(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository): Response
    {
        $project = new Project();
        $form = $this->createForm(CreateProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les participants de l'input hidden
            $participants = $request->request->get('participants');
            $participantEmails = explode(',', $participants);
            // Process team members' emails and add them to the project's team
            foreach ($participantEmails as $email) {
                $user = $userRepository->findOneBy(['email' => $email]);
                if ($user) {
                    $project->addTeam($user);
                }
            }
            $user = $this->getUser();
            $project->setCreator($user);
            $now = new \DateTimeImmutable();
            $project->setCreatedAt($now);
            $project->setUpdatedAt($now);
            $entityManager->persist($project);
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/project/{id}", name="app_project_details")
     */
    public function showDetails($id, ProjectRepository $projectRepository, Request $request): Response
    {
        $taskType = $request->query->get('taskType', 'allTasks'); // Par défaut, afficher "Mes tâches"
        $user = $this->getUser();
        $project = $projectRepository->findProjectByIdWithTeamAndTasks($id, $taskType, $user);
        $project = $project[0];
        VarDumper::dump($project);
        return $this->render('project/detail.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * @Route("/add-user-to-project/{projectId}", name="addUserToProject")
     */
    public function addUserToProject(int $projectId, UserRepository $userRepository, ProjectRepository $projectRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupère le projet
        $project = $projectRepository->find($projectId);
        if (!$project) {
            return new JsonResponse(['success' => false, 'message' => 'Le projet n\'existe pas.']);
        } elseif ($project->getCreator() != $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'ête pas le créateur du projet.']);
        }
        $email = $_GET['email'];
        // Vérifie si l'utilisateur existe dans la base de données
        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'L\'utilisateur n\'existe pas.']);
        }
        // Vérifie si l'utilisateur est déjà associé au projet
        if ($project->getTeam()->contains($user)) {
            return new JsonResponse(['success' => false, 'message' => 'L\'utilisateur est déjà dans le groupe.']);
        }
        $now = new \DateTimeImmutable();
        
        $project->setCreatedAt($now);
        $project->addTeam($user);
        $entityManager->persist($project);
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/remove-user-from-project/{projectId}", name="removeUserFromProject")
     */
    public function removeUserFromProject(int $projectId,  UserRepository $userRepository, ProjectRepository $projectRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $project = $projectRepository->find($projectId);
        if (!$project) {
            return new JsonResponse(['success' => false, 'message' => 'Le projet n\'existe pas.']);
        } elseif ($project->getCreator() != $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'êtes pas le créateur du projet.']);
        }
        $email = $_GET['email'];
        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'L\'utilisateur n\'existe pas.']);
        }
        // Met à jour toutes les tâches du projet où l'utilisateur est assigné
        $now = new \DateTimeImmutable();
        foreach ($project->getTasks() as $task) {
            if ($task->getAssignedUser() === $user) {
                $task->setAssignedUser(null);
                $task->setUpdatedAt($now);
            }
        }
        $project->setCreatedAt($now);
        $project->removeTeam($user);
        $entityManager->persist($project);
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
