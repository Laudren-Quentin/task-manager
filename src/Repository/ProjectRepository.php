<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @param User $user The user who's in Team Member Project
     * @return Project[] Returns an array of Project objects
     */
    public function findProjectsByTeamMember(User $user, $projectType) : array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p', '(SELECT COUNT(t1) FROM App\Entity\Task t1 WHERE t1.project = p) AS totalTasks', '(SELECT COUNT(t2) FROM App\Entity\Task t2 WHERE t2.project = p AND t2.completed = true) AS completedTasks', 'COUNT(u) AS teamSize')
            ->leftJoin('p.team', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->groupBy('p.id');
            switch ($projectType) {
                case 'new-projects':
                    $qb->orderBy('p.updatedAt', 'DESC');
                    break;
                case 'old-projects':
                    $qb->orderBy('p.updatedAt', 'ASC');
                    break;
                default:
                    $qb->orderBy('p.updatedAt', 'DESC');
                    break;
            }
        
            return $qb->getQuery()->getResult();
        
    }


    /**
     * @param User $user The user who created the projects
     * @return Project[] Returns an array of Project objects
     */
    public function findProjectsCreatedByUser(User $user, $projectType): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p', '(SELECT COUNT(t1) FROM App\Entity\Task t1 WHERE t1.project = p) AS totalTasks', '(SELECT COUNT(t2) FROM App\Entity\Task t2 WHERE t2.project = p AND t2.completed = true) AS completedTasks', 'COUNT(u) AS teamSize')
            ->leftJoin('p.team', 'u')
            ->where('p.creator = :user')
            ->setParameter('user', $user)
            ->groupBy('p.id');
            switch ($projectType) {
                case 'new-projects':
                    $qb->orderBy('p.updatedAt', 'DESC');
                    break;
                case 'old-projects':
                    $qb->orderBy('p.updatedAt', 'ASC');
                    break;
                default:
                    $qb->orderBy('p.updatedAt', 'ASC');
                    break;
            }
        
            return $qb->getQuery()->getResult();
    }

    public function findProjectById(int $id): ?Project
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findProjectByIdWithTeam(int $id): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.team', 't')
            ->addSelect('t') // Load the 'team' collection explicitly
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }


    public function findProjectByIdWithTeamAndTasks(int $id, $taskType, $user): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.team', 't')
            ->addSelect('t') // Load the 'team' collection explicitly
            ->leftJoin('p.tasks', 'ts')
            ->addSelect('ts') // Load the 'tasks' collection explicitly
            ->leftJoin('ts.category', 'c')
            ->addSelect('c') // Charger explicitement la collection 'category' liée à la tâche
            ->where('p.id = :id')
            ->setParameter('id', $id);

        switch ($taskType) {
            case 'myTasks':
                // Ajoutez une condition pour ne récupérer que les tâches de l'utilisateur actuellement connecté
                $qb->andWhere('ts.assignedUser = :currentUser')
                    ->setParameter('currentUser', $user);
                break;
            case 'allTasks':
                // Aucune condition supplémentaire nécessaire
                break;
            case 'completedTasks':
                // Ajoutez une condition pour ne récupérer que les tâches terminées
                $qb->andWhere('ts.completed = :completed')
                    ->setParameter('completed', true);
                break;
                // Vous pouvez ajouter d'autres cas ici pour différents types de tâches
            default:
                // Par défaut, ne rien faire de spécial
                break;
        }
        return $qb->orderBy('ts.completed', 'ASC')->addOrderBy('c.id', 'ASC')->getQuery()->getResult();
    }
}
