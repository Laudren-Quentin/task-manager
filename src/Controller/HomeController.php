<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Requests;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private ProjectRepository $projectRepository;
    private Security $security;

    public function __construct(ProjectRepository $projectRepository, Security $security)
    {
        $this->projectRepository = $projectRepository;
        $this->security = $security;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $projectType = $request->query->get('ProjectType');
        // Get the currently logged in user
        $user = $this->security->getUser();

         // Check if the user has the ROLE_ADMIN role
         if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Get projects created by the user (ROLE_ADMIN)
            $projects = $this->projectRepository->findProjectsCreatedByUser($user, $projectType);
            // dd($projects);
        } else {
            // Get projects where the user is a team member
            $projects = $this->projectRepository->findProjectsByTeamMember($user, $projectType);
        }

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
        ]);
    }
}
