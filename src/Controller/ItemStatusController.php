<?php

namespace App\Controller;

use App\Entity\ItemStatus;
use App\Form\ItemStatusType;
use App\Repository\ItemStatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/item/status')]
class ItemStatusController extends AbstractController
{
    #[Route('/', name: 'app_item_status_index', methods: ['GET'])]
    public function index(ItemStatusRepository $itemStatusRepository): Response
    {
        return $this->render('item_status/index.html.twig', [
            'item_statuses' => $itemStatusRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_item_status_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ItemStatusRepository $itemStatusRepository): Response
    {
        $itemStatus = new ItemStatus();
        $form = $this->createForm(ItemStatusType::class, $itemStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemStatusRepository->add($itemStatus, true);

            return $this->redirectToRoute('app_item_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('item_status/new.html.twig', [
            'item_status' => $itemStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_status_show', methods: ['GET'])]
    public function show(ItemStatus $itemStatus): Response
    {
        return $this->render('item_status/show.html.twig', [
            'item_status' => $itemStatus,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_status_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ItemStatus $itemStatus, ItemStatusRepository $itemStatusRepository): Response
    {
        $form = $this->createForm(ItemStatusType::class, $itemStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemStatusRepository->add($itemStatus, true);

            return $this->redirectToRoute('app_item_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('item_status/edit.html.twig', [
            'item_status' => $itemStatus,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_status_delete', methods: ['POST'])]
    public function delete(Request $request, ItemStatus $itemStatus, ItemStatusRepository $itemStatusRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$itemStatus->getId(), $request->request->get('_token'))) {
            $itemStatusRepository->remove($itemStatus, true);
        }

        return $this->redirectToRoute('app_item_status_index', [], Response::HTTP_SEE_OTHER);
    }
}
