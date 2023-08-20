<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CreateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du projet'
            ])
            ->add('team', TextType::class, [
                'label' => 'Membres de l\'équipe (adresse e-mail)',
                'required' => false,
                'attr' => ['placeholder' => 'Entrez des adresses e-mail séparées par des virgules']
            ])
            ->add('team', CollectionType::class, [
                'label' => 'Membres de l\'équipe (adresse e-mail)',
                'entry_type' => EmailType::class,
                'allow_add' => true,
                'attr' => [
                    'placeholder' => 'Entrez des adresses e-mail séparées par des virgules'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
