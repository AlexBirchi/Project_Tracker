<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Item;
use App\Entity\Project;
use App\Repository\CommentRepository;
use App\Repository\ItemRepository;
use App\Repository\ItemStatusRepository;
use App\Repository\ProjectUserRepository;
use App\Repository\SprintRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{project}/item')]
class ItemController extends AbstractController
{
    #[Route('/', name: 'app_item_index', methods: ['GET'])]
    public function index(ItemRepository $itemRepository): Response
    {
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findAll(),
        ]);
    }

    #[Route('/status/{id}', name: 'app_item_status_change', methods: ['GET'])]
    public function statusChange(Item $item, Project $project, Request $request,
                                 ItemStatusRepository $itemStatusRepository, ItemRepository $itemRepository): Response
    {
        $status = $request->GET( 'status');

        $newStatus = $itemStatusRepository->findOneBy(['name'=> $status]);

        $item->setItemStatus($newStatus);
        $itemRepository->update();

        return $this->redirectToRoute('sprints_active', ['project' => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'task_details', methods: ['GET'])]
    public function show(Item $item, UserRepository $repository, ItemStatusRepository $itemStatusRepository): Response
    {
        // get all the users
        $users = $repository->findUsersByProjectId($item->getProject()->getId());

        return $this->render('item/details.html.twig', [
            'item' => $item,
            'users' => $users,
            'statuses' => $itemStatusRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'task_create', methods: ['POST'])]
    public function create(Request $request, Project $project,
                           UserRepository $userRepository,
                           ItemStatusRepository $itemStatusRepository,
                           ItemRepository $itemRepository,
                           SprintRepository $sprintRepository): Response
    {
        $dataRequest = $request->request;

        $item = new Item();

        $item->setCreated(new \DateTime());
        $item->setUpdated(new \DateTime());
        $item->setReporter($this->getUser());
        $item->setAsignee($userRepository->find($request->get('assignee')));
        $item->setTitle($dataRequest->get('title'));
        $item->setDescription($dataRequest->get('summary'));
        $item->setPriority($dataRequest->get('priority'));
        $item->setType($dataRequest->get('issueType'));
        $item->setStoryPoints($dataRequest->get('storyPoints'));
        $item->setItemStatus($itemStatusRepository->findOneBy(array('name' => 'Open'))); // set the item's status as Open
        $item->setProject($project);

        if(count($project->getItems()) > 0) {
            $item->setNumber($project->getItems()->last()->getNumber() + 1);
        } else {
            $item->setNumber(1);
        }

        if ($dataRequest->get('sprint') != 0) {
            $sprint = $sprintRepository->find($dataRequest->get('sprint'));
            $sprint->addItem($item);
        }

        $itemRepository->add($item, true);

        // Some message
        $this->addFlash('success', 'Task created successfully.');

        return $this->redirectToRoute('sprints_backlog', ['project' => $project->getId()]);
    }

    #[Route('/edit/{id}', name: 'task_edit', methods: ['POST'])]
    public function edit(Request $request, Item $item, UserRepository $userRepository, SprintRepository $sprintRepository,
                         ItemRepository $itemRepository): Response
    {
        $dataRequest = $request->request;

        $item->setUpdated(new \DateTime());
        $item->setAsignee($userRepository->find($request->get('assignee')));
        $item->setTitle($dataRequest->get('title'));
        $item->setDescription($dataRequest->get('summary'));
        $item->setPriority($dataRequest->get('priority'));
        $item->setType($dataRequest->get('issueType'));
        $item->setStoryPoints($dataRequest->get('storyPoints'));

        if ($dataRequest->get('sprint') != 0) {
            $sprint = $sprintRepository->find($dataRequest->get('sprint'));
            $sprint->addItem($item);
        }

        $itemRepository->update();

        // Some message
        $this->addFlash('success', 'Task updated successfully.');

        // get all the users
        $users = $userRepository->findUsersByProjectId($item->getProject()->getId());

        return $this->render('item/details.html.twig', [
            'item' => $item,
            'users' => $users
        ]);
    }

    #[Route('/comment/{id}', name: 'task_comment', methods: ['POST'])]
    public function comment(Request $request, Item $item, UserRepository $userRepository, CommentRepository $commentRepository): Response
    {
        // Get back to details page
        $users = $userRepository->findUsersByProjectId($item->getProject()->getId());

        $dataRequest = $request->request;
        if(empty($dataRequest->get('comment'))){
            $this->addFlash('danger', 'The comment is too short.');
        }
        else {
            $comment = new Comment();
            $comment->setItem($item);
            $comment->setCreated(new \DateTime());
            $comment->setAuthor($this->getUser());
            $comment->setMessage($dataRequest->get('comment'));

            $commentRepository->add($comment, true);
            $this->addFlash('success', 'Comment added successfully!');
        }

        return $this->render('item/details.html.twig', [
            'item' => $item,
            'users' => $users
        ]);
    }

    #[Route('/{id}', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item, ItemRepository $itemRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $itemRepository->remove($item, true);
        }

        return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
