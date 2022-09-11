<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectUser;
use App\Entity\User;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Repository\ProjectUserRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository, UserRepository $userRepository, UserInterface $user): Response
    {
        $roles = $this->getUser()->getRoles();

        $isAdmin = false;
        $projects = [];
        if(in_array('ROLE_ADMIN', $roles)) {
            $projects = $projectRepository->findAll();
            $isAdmin = true;
        } else {
            $projects = $projectRepository->findProjectsByUserId($user->getId());
        }


        $users = $userRepository->findAll();

        return $this->render('project/index1.html.twig', [
            'projects' => $projects,
            'users' => $users,
            'admin' => $isAdmin,
        ]);
    }

    #[Route('/create', name: 'project_create', methods: ['POST'])]
    public function create(Request $request, ProjectRepository $projectRepository,
                           ProjectUserRepository $projectUserRepository): Response
    {
        $dataRequest = $request->request;

        $project = new Project();

        $project->setName($dataRequest->get('name'));
        $project->setTag($dataRequest->get('tag'));
        $project->setDescription($dataRequest->get('summary'));

        $projectRepository->add($project, true);

        /* Assign the creator to the project */
        $projectUser = new ProjectUser();
        $projectUser->setUser($this->getUser());
        $projectUser->setRole(User::ROLE_PRODUCT_OWNER);
        $projectUser->setProjects($project);

        $projectUserRepository->add($projectUser, true);

        // Some message
        $this->addFlash('success', 'Project created successfully.');

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->add($project, true);

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project,
                           ProjectRepository $projectRepository,
                           ProjectUserRepository $projectUserRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            // Instead of Cascade: DELETE
            $projectUsers = $projectUserRepository->findBy(['projects'=>$project->getId()]);
            foreach ($projectUsers as $projectUser)
            {
                $projectUserRepository->remove($projectUser, true);
            }

            $projectRepository->remove($project, true);
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/current/{id}', name: 'app_project_current', methods: ['GET'])]
    public function current(Project $project): Response
    {
        return $this->render('project/_current.html.twig', [
            'project' => $project
        ]);
    }
}
