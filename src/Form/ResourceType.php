<?php

namespace App\Form;

use App\Entity\Module;
use App\Entity\Resource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('type', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Fichier (PDF, DOCX)' => 'file',
                    'Lien externe (URL)' => 'link',
                    'Texte riche' => 'text',
                ],
            ])
            ->add('file', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'label' => 'Fichier (si type Fichier)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document valide (PDF, DOCX)',
                    ])
                ],
            ])
            ->add('content', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => 'Contenu (URL ou Texte)',
                'required' => false,
            ])
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }
}
