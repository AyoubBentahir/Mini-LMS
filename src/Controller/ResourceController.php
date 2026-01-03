<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\ResourceType;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/resource')]
final class ResourceController extends AbstractController
{
    #[Route(name: 'app_resource_index', methods: ['GET'])]
    public function index(ResourceRepository $resourceRepository): Response
    {
        return $this->render('resource/index.html.twig', [
            'resources' => $resourceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_resource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        $resource = new Resource();
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $resourceFile */
            $resourceFile = $form->get('file')->getData();

            if ($resourceFile) {
                $originalFilename = pathinfo($resourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resourceFile->guessExtension();

                try {
                    $resourceFile->move(
                        $this->getParameter('resources_directory'),
                        $newFilename
                    );
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    // handle exception
                }

                $resource->setContent($newFilename);
            }

            $entityManager->persist($resource);
            $entityManager->flush();

            return $this->redirectToRoute('app_resource_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('resource/new.html.twig', [
            'resource' => $resource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_resource_show', methods: ['GET'])]
    public function show(Resource $resource): Response
    {
        return $this->render('resource/show.html.twig', [
            'resource' => $resource,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_resource_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Resource $resource, EntityManagerInterface $entityManager, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $resourceFile */
            $resourceFile = $form->get('file')->getData();

            if ($resourceFile) {
                $originalFilename = pathinfo($resourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resourceFile->guessExtension();

                try {
                    $resourceFile->move(
                        $this->getParameter('resources_directory'),
                        $newFilename
                    );
                } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
                    // handle exception
                }

                $resource->setContent($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_resource_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('resource/edit.html.twig', [
            'resource' => $resource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_resource_delete', methods: ['POST'])]
    public function delete(Request $request, Resource $resource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$resource->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($resource);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_resource_index', [], Response::HTTP_SEE_OTHER);
    }
}
