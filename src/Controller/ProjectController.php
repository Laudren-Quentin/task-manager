<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\CreateProjectType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class ProjectController extends AbstractController
{
    #[Route('/project', name: 'app_project')]
    public function index(): Response
    {
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    #[Route('/project/create', name: 'app_create_project')]
    public function createProject(EntityManagerInterface $entityManager ,Request $request, UserRepository $userRepository): Response
    {
        $project = new Project();
        $form = $this->createForm(CreateProjectType::class, $project);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            // Retrieve the participants from the hidden input field
            $participants = $request->request->get('participants');
            $participantEmails = explode(',', $participants);

            // Process team members' emails and add them to the project's team
            foreach ($participantEmails as $email) {
                $user = $userRepository->findOneBy(['email' => $email]);
                if ($user) {
                    $project->addTeam($user);
                }
            }

            // Set the current user as the creator of the project
            $user = $this->getUser();
            $project->setCreator($user);

            // Set createdAt and updatedAt
            $now = new \DateTimeImmutable();
            $project->setCreatedAt($now);
            $project->setUpdatedAt($now);

            $entityManager->persist($project);
            $entityManager->flush();

            // Rediriger ou afficher un message de succès
            return $this->redirectToRoute('app_home');
        }

        return $this->render('project/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
