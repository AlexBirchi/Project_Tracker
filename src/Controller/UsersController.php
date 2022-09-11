<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectUser;
use App\Entity\User;
use App\Repository\ProjectUserRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{project}')]
class UsersController extends AbstractController
{
    #[Route('/{selected}', name: 'app_project_users', methods: ['GET'])]
    public function projectUsers(UserRepository $userRepository, Project $project, string $selected=null): Response
    {
        return $this->render('users/_project_users.html.twig', [
            'users' => $userRepository->findUsersByProjectId($project->getId()),
            'selected' => $selected
        ]);
    }

    #[Route('/team/users', name: 'app_project_team', methods: ['GET'])]
    public function team(ProjectUserRepository $projectUserRepository, Project $project): Response
    {
        // User role
        $user = $this->getUser();
        $userProjectRole = $projectUserRepository->findOneBy(['user' => $user, 'projects' => $project->getId()]);

        $projectUsers = $projectUserRepository->findBy(['projects' => $project->getId()]);

        return $this->render('users/team.html.twig', [
            'users' => $projectUsers,
            'roles'=> User::ROLES,
            'projectRole' => $userProjectRole,
        ]);
    }

    #[Route('/team/add', name: 'app_project_assign', methods: ['POST', 'GET'])]
    public function assign(ProjectUserRepository $projectUserRepository, UserRepository $userRepository,
                           Project $project, Request $request): Response
    {
        $dataRequest = $request->request;

        $role = $dataRequest->get('role');
        if (!in_array($role, User::ROLES)) {
            $this->addFlash('danger', "Invalid role provided.");
            return $this->redirectToRoute('app_project_team', ['project'=> $project->getId()], Response::HTTP_SEE_OTHER);
        }

        $user = $userRepository->findOneBy(['email'=> $dataRequest->get('email')]);
        if(is_null($user)){
            $this->addFlash('danger', "User {$dataRequest->get('email')} not found.");
        }else {
            $userProject = new ProjectUser();
            $userProject->setUser($user);
            $userProject->setRole($role);
            $userProject->setProjects($project);
            $projectUserRepository->add($userProject, true);

            $this->addFlash('success', "User {$dataRequest->get('email')} assigned successfully.");
        }

        return $this->redirectToRoute('app_project_team', ['project'=> $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/team/project-user/{id}', name: 'app_project_role', methods: ['POST'])]
    public function role(ProjectUserRepository $projectUserRepository, ProjectUser $projectUser,
                         Project $project, Request $request): Response
    {
        $dataRequest = $request->request;

        $role = $dataRequest->get('role');
        if (!in_array($role, User::ROLES)) {
            $this->addFlash('danger', "Invalid role provided.");
            return $this->redirectToRoute('app_project_team', ['project'=> $project->getId()], Response::HTTP_SEE_OTHER);
        }

        $projectUser->setRole($role);
        $projectUserRepository->update();

        $this->addFlash('success', "User {$projectUser->getUser()->getEmail()}'s role has been changed successfully.");

        return $this->redirectToRoute('app_project_team', ['project'=> $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/team/project-user/remove/{id}', name: 'app_project_user_remove', methods: ['POST'])]
    public function remove(ProjectUserRepository $projectUserRepository, ProjectUser $projectUser, Project $project): Response
    {
        $projectUserRepository->remove($projectUser, true);
        $this->addFlash('success', "User {$projectUser->getUser()->getEmail()} has been removed from project successfully.");

        return $this->redirectToRoute('app_project_team', ['project'=> $project->getId()], Response::HTTP_SEE_OTHER);
    }
}