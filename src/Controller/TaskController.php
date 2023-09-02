<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
            
             // Set createdAt and updatedAt
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
}
