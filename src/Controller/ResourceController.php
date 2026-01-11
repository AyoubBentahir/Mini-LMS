<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Form\ResourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/resource')]
final class ResourceController extends AbstractController
{
    #[Route('/new', name: 'app_resource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $resource = new Resource();
        
        // Get module_id from query parameter if provided
        $moduleId = $request->query->get('module_id');
        $redirectModuleId = $moduleId;
        
        // Pre-select module if module_id is provided
        if ($moduleId) {
            $module = $entityManager->getRepository(\App\Entity\Module::class)->find($moduleId);
            if ($module) {
                $resource->setModule($module);
            }
        }
        
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $resourceFile */
            $resourceFile = $form->get('file')->getData();

            if ($resourceFile) {
                // If a file is uploaded, process it and generate URL
                $originalFilename = pathinfo($resourceFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resourceFile->guessExtension();

                try {
                    $resourceFile->move(
                        $this->getParameter('resources_directory'),
                        $newFilename
                    );
                    
                    // Generate public URL for the file
                    $publicUrl = $this->generateUrl('app_resource_download', [
                        'filename' => $newFilename
                    ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
                    
                    // Store the public URL in the content field (overwrite any manual input)
                    $resource->setContent($publicUrl);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier: ' . $e->getMessage());
                    return $this->render('resource/new.html.twig', [
                        'resource' => $resource,
                        'form' => $form,
                        'module_id' => $redirectModuleId,
                    ]);
                }
            }
            // If no file uploaded, the content from the form will be used (URL or text)

            $entityManager->persist($resource);
            $entityManager->flush();

            $this->addFlash('success', 'Ressource créée avec succès !');

            // Redirect to module show if module is set, otherwise to module index
            if ($resource->getModule()) {
                return $this->redirectToRoute('app_module_show', ['id' => $resource->getModule()->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_module_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('resource/new.html.twig', [
            'resource' => $resource,
            'form' => $form,
            'module_id' => $redirectModuleId,
        ]);
    }

    #[Route('/download/{filename}', name: 'app_resource_download', methods: ['GET'])]
    public function download(string $filename): Response
    {
        $filePath = $this->getParameter('resources_directory') . '/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        return $this->file($filePath);
    }

    #[Route('/{id}', name: 'app_resource_delete', methods: ['POST'])]
    public function delete(Request $request, Resource $resource, EntityManagerInterface $entityManager): Response
    {
        $moduleId = $resource->getModule() ? $resource->getModule()->getId() : null;
        
        if ($this->isCsrfTokenValid('delete'.$resource->getId(), $request->getPayload()->getString('_token'))) {
            // Delete the file if it exists
            if ($resource->getContent() && str_contains($resource->getContent(), '/resource/download/')) {
                $filename = basename(parse_url($resource->getContent(), PHP_URL_PATH));
                $filePath = $this->getParameter('resources_directory') . '/' . $filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $entityManager->remove($resource);
            $entityManager->flush();
        }

        // Redirect back to the module page if module exists
        if ($moduleId) {
            return $this->redirectToRoute('app_module_show', ['id' => $moduleId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_module_index', [], Response::HTTP_SEE_OTHER);
    }
}
