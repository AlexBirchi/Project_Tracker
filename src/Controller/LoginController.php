<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Muta view
class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout() {}

    #[Route('/{project}/user', name: 'app_login_user', methods: ['GET'])]
    public function current(ProjectUserRepository $projectUserRepository, Project $project): Response
    {
        $user = $this->getUser();
        $userProject = $projectUserRepository->findOneBy(['user'=>$user, 'projects' => $project]);

        return $this->render('login/_logged_user.html.twig', [
            'user' => $userProject,
        ]);
    }
}
