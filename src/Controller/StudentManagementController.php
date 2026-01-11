<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\StudentType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * CONTROLEUR GESTION DES ÉTUDIANTS (ADMINISTRATION)
 * 
 * Rôle : Ce contrôleur est réservé à l'ADMINISTRATEUR (IsGranted('ROLE_ADMIN')).
 * Il sert à GÉRER les compte étudiants (CRUD) :
 * - Lister les étudiants (index)
 * - Créer un compte manuellement (new)
 * - Modifier un compte (edit)
 * - Activer/Bannir (toggleStatus)
 * 
 * NE PAS CONFONDRE AVEC : StudentDashboardController (qui est pour l'élève).
 */
#[Route('/admin/students')]
#[IsGranted('ROLE_ADMIN')]
class StudentManagementController extends AbstractController
{
    /**
     * LISTE (Read)
     * Affiche le tableau de tous les étudiants inscrits dans la base de données.
     */
    #[Route('', name: 'app_student_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // On demande au repository de ne trouver QUE ceux qui ont le role ROLE_STUDENT
        $students = $userRepository->findByRole('ROLE_STUDENT');

        return $this->render('student_management/index.html.twig', [
            'students' => $students,
        ]);
    }

    /**
     * CRÉATION (Create)
     * Formulaire pour ajouter manuellement un étudiant par l'admin.
     */
    #[Route('/new', name: 'app_student_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsActive(true);
        // On force le rôle "ÉTUDIANT" ici
        $user->setRoles(['ROLE_STUDENT']);
        
        $form = $this->createForm(StudentType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe obligatoire
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Étudiant créé avec succès!');
            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student_management/new.html.twig', [
            'student' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('student_management/show.html.twig', [
            'student' => $user,
        ]);
    }

    /**
     * MODIFICATION (Update)
     * Permet à l'admin de corriger le nom, email, etc. d'un étudiant.
     */
    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(StudentType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Étudiant modifié avec succès!');
            return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student_management/edit.html.twig', [
            'student' => $user,
            'form' => $form,
        ]);
    }

    /**
     * ACTIVATION / DÉSACTIVATION (Delete soft)
     * Bannir ou débannir un étudiant.
     */
    #[Route('/{id}/toggle-status', name: 'app_student_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsActive(!$user->isIsActive());
        $entityManager->flush();

        $status = $user->isIsActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Étudiant {$status} avec succès!");

        return $this->redirectToRoute('app_student_index', [], Response::HTTP_SEE_OTHER);
    }
}
