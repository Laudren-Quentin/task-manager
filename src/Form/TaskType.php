<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Category; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $project = $options['data']->getProject(); // Obtenez le projet de la tâche

        $builder
            ->add('title')
            ->add('description')
            ->add('assignedUser', ChoiceType::class, [
                'choices' => $this->getUserChoices($project),
                'required' => false,
                'placeholder' => 'choisir un utilisateur',
                'label' => 'Utilisateur assigné : ',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'label', // Changer 'label' pour correspondre au champ de catégorie que vous souhaitez afficher
                'placeholder' => 'Selectionner un choix',
                'required' => true, // Modifiez ceci en fonction de vos besoins
                'label' => 'Niveau d\'urgence : ',
                'query_builder' => function (EntityRepository $er) {
                    // Cette fonction permet de construire la requête pour récupérer les catégories
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.label', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }

    private function getUserChoices(Project $project)
    {
        $userChoices = [];

        foreach ($project->getTeam() as $user) {
            $userChoices[$user->getEmail()] = $user;
        }

        return $userChoices;
    }
}
