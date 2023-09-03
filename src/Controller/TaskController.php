<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\VarDumper\VarDumper;

class TaskController extends AbstractController
{

    /**
     * @Route("/project/{id}/create-task", name="app_create_task")
     */
    public function createTask($id, Request $request, ProjectRepository $projectRepository, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        // Récupérez le projet associé à l'ID fourni avec toutes les informations nécéssaire 
        $project = $projectRepository->findProjectById($id);
        VarDumper::dump($project);

        // Créez une nouvelle tâche liée au projet
        $task = new Task();
        $task->setProject($project);
        $task->setCreator($user); // Le créateur de la tâche est l'utilisateur connecté
        $task->setCompleted(false);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        VarDumper::dump($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $task->setCreatedAt($now);
            $task->setUpdatedAt($now);

            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('app_project_details', ['id' => $id]);
        }
        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/project/{projectId}/edit-task/{taskId}", name="app_edit_task")
     */
    public function editTask($projectId, $taskId, Request $request, ProjectRepository $projectRepository, UserInterface $user, EntityManagerInterface $entityManager): Response
    {
        $project = $projectRepository->find($projectId);
        $task = $entityManager->getRepository(Task::class)->find($taskId);

        if ($task->getCreator() != $this->getUser()) {
            throw $this->createNotFoundException('Vous ne pouvez pas modifier cette tâche');
        }
        // Check si la tâche appartient au projet
        if (!$task || $task->getProject() !== $project) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $task->setUpdatedAt($now);
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_details', ['id' => $projectId]);
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/task/delete/{id}", name="app_delete_task", methods={"DELETE"})
     */
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérez la tâche à supprimer depuis la base de données
        $task = $entityManager->getRepository(Task::class)->find($id);
        if ($task->getCreator() == $this->getUser()) {
            $entityManager->remove($task);
            $entityManager->flush();
        } else {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette tâche.');
        }

        return new JsonResponse(['message' => 'Tâche supprimée avec succès'], 200);
    }

    /**
     * @Route("/task/validate/{taskId}", name="validateTask")
     */
    public function validateTask(int $taskId, EntityManagerInterface $entityManager): JsonResponse
    {
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        if (!$task) {
            return new JsonResponse(['success' => false, 'message' => 'La tâche n\'existe pas.']);
        }elseif($task->getAssignedUser() != $this->getUser()){
            return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas valider une tâche qui ne vous est pas assigné']);
        }
        $task->setCompleted(true);
        $entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
