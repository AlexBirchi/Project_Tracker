<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Sprint;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\ItemStatusRepository;
use App\Repository\ProjectRepository;
use App\Repository\ProjectUserRepository;
use App\Repository\SprintRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{project}/sprints')]
class SprintController extends AbstractController
{
    #[Route('/', name: 'sprints_backlog', methods: ['GET'])]
    public function index(Project $project, UserRepository $userRepository,
                          ItemRepository $itemRepository, ProjectUserRepository $projectUserRepository): Response
    {
        // User role
        $user = $this->getUser();
        $userProjectRole = $projectUserRepository->findOneBy(['user' => $user, 'projects' => $project->getId()]);

        $itemsWithoutSprint = $itemRepository->findItemsWithoutSprint($project->getId());

        return $this->render('sprints/index.html.twig', [
            'project' => $project,
            'users' => $userRepository->findAll(),
            'others' => $itemsWithoutSprint,
            'projectRole' => $userProjectRole,
        ]);
    }

    #[Route('/active', name: 'sprints_active', methods: ['GET'])]
    public function active(Project $project, SprintRepository $sprintRepository, ItemStatusRepository $itemStatusRepository): Response
    {
        $activeSprint = $sprintRepository->findOneBy(['status' =>Sprint::STATUS_ACTIVE, 'project' => $project->getId()]);
        $itemStatuses = $itemStatusRepository->findAll();

        return $this->render('sprints/active.html.twig', [
            'project' => $project,
            'sprint' => $activeSprint,
            'statuses' => $itemStatuses,
        ]);
    }

    #[Route('/available', name: 'sprints_available', methods: ['GET'])]
    public function available(Project $project): Response
    {
        return $this->render('sprints/_available.html.twig', [
            'sprints' => $project->getSprints(),
        ]);
    }

    #[Route('/create', name: 'sprints_create', methods: ['POST', 'GET'])]
    public function create(Project $project, SprintRepository $sprintRepository): Response
    {
        $sprint = new Sprint();

        $sprint->setProject($project);
        $sprint->setCreated(new \DateTime());
        $sprint->setUpdated(new \DateTime());
        $sprint->setStatus(Sprint::STATUS_UPCOMING);

        // Setting sprint number
        $sprints = $sprintRepository->findBy(['project'=>$project]);
        if(count($sprints) > 0) {
            $sprint->setNumber(end($sprints)->getNumber() + 1);
        } else {
            $sprint->setNumber(1);
        }

        $sprintRepository->add($sprint, true);

        return $this->redirectToRoute('sprints_backlog', ['project' => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/activate/{id}', name: 'sprints_activate', methods: ['POST', 'GET'])]
    public function activate(Project $project, Sprint $sprint, SprintRepository $sprintRepository): Response
    {
        if(count($sprintRepository->findBy(['status' => Sprint::STATUS_ACTIVE, 'project'=>$project])) > 0){
            $this->addFlash('danger', 'There is already an active sprint.');
        }else {
            $sprint->setStatus(Sprint::STATUS_ACTIVE);
            $sprint->setUpdated(new \DateTime());
            $sprintRepository->update();
            $this->addFlash('success', "Sprint {$sprint->getNumber()} has been set as active!");
        }

        return $this->redirectToRoute('sprints_backlog', ['project' => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/close/{id}', name: 'sprints_close', methods: ['POST', 'GET'])]
    public function close(Project $project, Sprint $sprint, SprintRepository $sprintRepository): Response
    {
        $sprint->setStatus(Sprint::STATUS_CLOSED);
        $sprint->setUpdated(new \DateTime());
        $sprintRepository->update();
        $this->addFlash('success', "Sprint {$sprint->getNumber()} has been closed successfully!");

        return $this->redirectToRoute('sprints_backlog', ['project' => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'sprints_delete', methods: ['POST'])]
    public function delete(Request $request, Sprint $sprint,
                           Project $project, SprintRepository $sprintRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sprint->getId(), $request->request->get('_token'))) {
            $sprintRepository->remove($sprint, true);
        }

        return $this->redirectToRoute('sprints_backlog', ['project' => $project->getId()], Response::HTTP_SEE_OTHER);
    }
}