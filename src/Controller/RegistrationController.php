<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    /**
     * PAGE D'INSCRIPTION (Register)
     * Permet à un visiteur de créer son compte.
     * 
     * IMPORTANT : Par défaut, tout nouvel inscrit est un ÉTUDIANT (ROLE_STUDENT).
     * Les professeurs et admins ne peuvent pas s'inscrire ici (ils sont créés par un admin).
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        // Création du formulaire basé sur RegistrationType
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. Définir les valeurs par défaut
            $user->setRoles(['ROLE_STUDENT']); // Sécurité : On force le rôle étudiant
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsActive(true); // Compte actif par défaut

            // 2. Sécuriser le mot de passe (Hachage)
            // On ne stocke JAMAIS le mot de passe en clair.
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData() // Récupéré du champ non mappé du formulaire
                )
            );

            // 3. Sauvegarder en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // 4. Succès et redirection
            $this->addFlash('success', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
