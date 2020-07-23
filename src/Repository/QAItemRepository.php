<?php

namespace App\Repository;

use App\Entity\QAItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QAItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method QAItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method QAItem[]    findAll()
 * @method QAItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QAItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QAItem::class);
    }

    // /**
    //  * @return QAItem[] Returns an array of QAItem objects
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
    public function findOneBySomeField($value): ?QAItem
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
