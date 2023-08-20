<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function findProjectsByTeamMember(User $user)
    {
        return $this->createQueryBuilder('p')
            ->select('p', '(SELECT COUNT(t1) FROM App\Entity\Task t1 WHERE t1.project = p) AS totalTasks', '(SELECT COUNT(t2) FROM App\Entity\Task t2 WHERE t2.project = p AND t2.completed = true) AS completedTasks', 'COUNT(u) AS teamSize')
            ->leftJoin('p.team', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }


    /**
     * @param User $user The user who created the projects
     * @return Project[] Returns an array of Project objects
     */
    public function findProjectsCreatedByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', '(SELECT COUNT(t1) FROM App\Entity\Task t1 WHERE t1.project = p) AS totalTasks', '(SELECT COUNT(t2) FROM App\Entity\Task t2 WHERE t2.project = p AND t2.completed = true) AS completedTasks', 'COUNT(u) AS teamSize')
            ->leftJoin('p.team', 'u')
            ->where('p.creator = :user')
            ->setParameter('user', $user)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }
}
