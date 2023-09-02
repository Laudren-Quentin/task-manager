<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Task;
use App\Repository\ProjectRepository;
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
