<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/module')]
#[IsGranted('ROLE_TEACHER')]
final class ModuleController extends AbstractController
{
    #[Route(name: 'app_module_index', methods: ['GET'])]
    public function index(ModuleRepository $moduleRepository): Response
    {
        return $this->render('module/index.html.twig', [
            'modules' => $moduleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_module_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CourseRepository $courseRepository): Response
    {
        // Récupérer le cours depuis le paramètre
        $courseId = $request->query->get('course_id');
        if (!$courseId) {
            throw $this->createNotFoundException('Le paramètre course_id est requis.');
        }

        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Cours introuvable.');
        }

        // Vérifier que c'est bien le cours de cet enseignant
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas le propriétaire de ce cours.");
        }

        $module = new Module();
        $module->setCourse($course);

        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('app_course_show', ['id' => $course->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('module/new.html.twig', [
            'module' => $module,
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_module_show', methods: ['GET'])]
    public function show(Module $module): Response
    {
        return $this->render('module/show.html.twig', [
            'module' => $module,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_module_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Module $module, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que c'est bien le cours de cet enseignant
        if ($module->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas le propriétaire de ce module.");
        }

        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_course_show', ['id' => $module->getCourse()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('module/edit.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_module_delete', methods: ['POST'])]
    public function delete(Request $request, Module $module, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que c'est bien le cours de cet enseignant
        if ($module->getCourse()->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas le propriétaire de ce module.");
        }

        $courseId = $module->getCourse()->getId();

        if ($this->isCsrfTokenValid('delete'.$module->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($module);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_course_show', ['id' => $courseId], Response::HTTP_SEE_OTHER);
    }
}
