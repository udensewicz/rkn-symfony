<?php

namespace App\Repository;

use App\Entity\QACategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QACategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method QACategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method QACategory[]    findAll()
 * @method QACategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QACategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QACategory::class);
    }

    // /**
    //  * @return QACategory[] Returns an array of QACategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QACategory
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
