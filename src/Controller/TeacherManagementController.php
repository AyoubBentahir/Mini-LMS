<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/teachers')]
#[IsGranted('ROLE_ADMIN')]
class TeacherManagementController extends AbstractController
{
    #[Route('', name: 'app_teacher_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $teachers = $userRepository->findByRole('ROLE_TEACHER');

        return $this->render('teacher_management/index.html.twig', [
            'teachers' => $teachers,
        ]);
    }

    #[Route('/new', name: 'app_teacher_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsActive(true);
        $user->setRoles(['ROLE_TEACHER']);
        
        $form = $this->createForm(TeacherType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Enseignant créé avec succès!');
            return $this->redirectToRoute('app_teacher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('teacher_management/new.html.twig', [
            'teacher' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_teacher_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('teacher_management/show.html.twig', [
            'teacher' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_teacher_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(TeacherType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Enseignant modifié avec succès!');
            return $this->redirectToRoute('app_teacher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('teacher_management/edit.html.twig', [
            'teacher' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'app_teacher_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsActive(!$user->isIsActive());
        $entityManager->flush();

        $status = $user->isIsActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Enseignant {$status} avec succès!");

        return $this->redirectToRoute('app_teacher_index', [], Response::HTTP_SEE_OTHER);
    }
}
