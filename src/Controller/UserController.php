<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/check-user-exist', name: 'app_check_user')]
    public function checkUser(Request $request, UserRepository $userRepository): JsonResponse
    {
        $email = $request->query->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        return new JsonResponse(['exists' => $user !== null]);
    }

    /**
     * @Route("/user/edit", name="app_edit_user", methods={"POST", "GET"})
     */
    public function edit(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Récupérez l'utilisateur actuellement connecté
        $user = $this->getUser();
        // $user = $userRepository->findById($user->getId());

        // Créez un formulaire de modification de mot de passe
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setEmail($form->get('email')->getData());
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/delete", name="app_delete_user", methods={"POST", "GET"})
     */
    public function delete(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        // Récupérez l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Créez un formulaire pour confirmer la suppression
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer la suppression',
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si le mot de passe est correct
            if ($passwordEncoder->isPasswordValid($user, $form->get('password')->getData())) {
                // Supprimez l'utilisateur de la base de données
                $tasks = $entityManager->getRepository(Task::class)->findBy(['assignedUser' => $user]);
                foreach ($tasks as $task) {
                    $task->setAssignedUser(null);
                    $entityManager->persist($task);
                }
                // $entityManager->flush();
                $entityManager->remove($user);
                $entityManager->flush();

                // Déconnectez l'utilisateur
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            // Redirigez vers une page de confirmation ou la page d'accueil
            return $this->redirectToRoute('app_home');

                // Redirigez vers une page de confirmation ou la page d'accueil
                return $this->redirectToRoute('app_home');
            } else {
                // Mot de passe incorrect, affichez une erreur
                $this->addFlash('danger', 'Mot de passe incorrect. La suppression du compte a échoué.');
            }
        }

        return $this->render('user/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
