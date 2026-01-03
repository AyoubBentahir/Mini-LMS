<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Enseignant' => 'ROLE_TEACHER',
                    'Étudiant' => 'ROLE_STUDENT',
                ],
                'multiple' => true,
                'expanded' => true, // Checkboxes
                'label' => 'Rôles'
            ])
            ->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
                'hash_property_path' => 'password',
                'mapped' => false,
                'required' => $options['required'] ?? true, // Pas obligé si edit (à gérer plus tard)
            ])
            ->add('nom')
            ->add('prenom')
            ->add('isActive', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Compte Actif',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
