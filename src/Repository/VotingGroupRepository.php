<?php

namespace App\Repository;

use App\Entity\VotingGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VotingGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method VotingGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method VotingGroup[]    findAll()
 * @method VotingGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VotingGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VotingGroup::class);
    }

    public function getPeopleVoted($votingGroup)
    {
        $peopleVoted = [];
        $votings = $votingGroup->getVotings();
        foreach ($votings as &$voting) {
            $votes = $voting->getVotes();
            foreach ($votes as &$vote)
                if (!in_array($vote->getRoleValidityID(), $peopleVoted)) {
                    array_push($peopleVoted, $vote->getRoleValidityId());
                }
        }

        return $peopleVoted;
    }

    // /**
    //  * @return VotingGroup[] Returns an array of VotingGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VotingGroup
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
