<?php

namespace App\Repository;

use App\Entity\ResearchGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResearchGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResearchGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResearchGroup[]    findAll()
 * @method ResearchGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResearchGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResearchGroup::class);
    }

    // /**
    //  * @return ResearchGroup[] Returns an array of ResearchGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResearchGroup
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
