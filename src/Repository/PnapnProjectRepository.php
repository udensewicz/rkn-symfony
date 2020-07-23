<?php

namespace App\Repository;

use App\Entity\PnapnProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PnapnProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method PnapnProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method PnapnProject[]    findAll()
 * @method PnapnProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PnapnProjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PnapnProject::class);
    }

    // /**
    //  * @return PnapnProject[] Returns an array of PnapnProject objects
    //  */

    public function listAllowedMeetingProjects($voting)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.Voting = :val')
            ->setParameter('val', $voting)
            ->andWhere('p.presentationOrder > 0 OR p.presentationOrder IS NULL')
            ->orderBy('p.presentationOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function listAllMeetingProjects($voting)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.Voting = :val')
            ->setParameter('val', $voting)
            ->orderBy('p.presentationOrder', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


    /*
    public function findOneBySomeField($value): ?PnapnProject
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
