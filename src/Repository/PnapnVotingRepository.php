<?php

namespace App\Repository;

use App\Entity\PnapnVoting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PnapnVoting|null find($id, $lockMode = null, $lockVersion = null)
 * @method PnapnVoting|null findOneBy(array $criteria, array $orderBy = null)
 * @method PnapnVoting[]    findAll()
 * @method PnapnVoting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PnapnVotingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PnapnVoting::class);
    }

    // /**
    //  * @return PnapnVoting[] Returns an array of PnapnVoting objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PnapnVoting
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
