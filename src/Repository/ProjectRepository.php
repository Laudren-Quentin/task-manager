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
            ->leftJoin('p.team', 't')
            ->andWhere('t.id = :userId')
            ->setParameter('userId', $user->getId())
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
            ->where('p.creator = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }



//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
