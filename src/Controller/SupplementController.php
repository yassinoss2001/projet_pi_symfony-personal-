<?php

namespace App\Controller;

use App\Entity\Supplement;
use App\Form\SupplementType;
use App\Repository\SupplementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/supplement')]
class SupplementController extends AbstractController
{
    #[Route('/', name: 'app_supplement_index', methods: ['GET'])]
    public function index(SupplementRepository $supplementRepository): Response
    {
        return $this->render('supplement/index.html.twig', [
            'supplements' => $supplementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_supplement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplement = new Supplement();
        $form = $this->createForm(SupplementType::class, $supplement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
       
                if ($imageFile) {
                $imageFileName = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $imageFileName
                );
                $supplement->setImage($imageFileName);
            }
            $entityManager->persist($supplement);
            $entityManager->flush();

            return $this->redirectToRoute('app_supplement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('supplement/new.html.twig', [
            'supplement' => $supplement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_supplement_show', methods: ['GET'])]
    public function show(Supplement $supplement): Response
    {
        return $this->render('supplement/show.html.twig', [
            'supplement' => $supplement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_supplement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Supplement $supplement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SupplementType::class, $supplement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingImage = $supplement->getImage();
        $newImage = $form->get('image')->getData();

        if (!$newImage) {
            // If no new image is provided, keep the existing one
            $supplement->setImage($existingImage);
        } else {
            // If a new image is provided, handle it as in the 'new' action
            $imageFileName = uniqid().'.'.$newImage->guessExtension();
            $newImage->move(
                $this->getParameter('images_directory'),
                $imageFileName
            );
            $supplement->setImage($imageFileName);
        }
            $entityManager->flush();

            return $this->redirectToRoute('app_supplement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('supplement/edit.html.twig', [
            'supplement' => $supplement,
            'form' => $form,
        ]);
    }
//delete supplement
    #[Route('/{id}', name: 'app_supplement_delete', methods: ['POST'])]
    public function delete(Request $request, Supplement $supplement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$supplement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($supplement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_supplement_index', [], Response::HTTP_SEE_OTHER);
    }
}
