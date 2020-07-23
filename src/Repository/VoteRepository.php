<?php

namespace App\Repository;

use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vote[]    findAll()
 * @method Vote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    public function getPeopleVoted($votingGroup)
    {
        $sampleVoting = $votingGroup->getVotings()[0];
        $votes = $this->findBy(['Voting' => $sampleVoting]);
        $peopleVoted = [];
        foreach ($votes as &$vote)
            if (!in_array($vote->getRoleValidityID(), $peopleVoted)) {
                array_push($peopleVoted, $vote->getRoleValidityId());
            }
        return $peopleVoted;
    }

    public function getOpiekunScores($votingGroup, $cut){
        //possibly check for invalid votes here although there shouldn't be any
        $opiekunVotings = $votingGroup->getVotings();
        foreach ($opiekunVotings as &$opiekunVoting){
            $cutVotes = [];
            $query = $this->createQueryBuilder('v')
                ->andWhere('v.Voting = :val')
                ->setParameter('val', $opiekunVoting)
                ->andWhere('v.vote IS NOT NULL')
//                ->andWhere('v.roleValidityId IN (:validvoters)')
//                ->setParameter('validvoters', $validVoters)
                ->select('v.id, v.vote');
            $min = $query
                ->orderBy('v.vote', 'ASC')
                ->setMaxResults($cut)
                ->getQuery()
                ->getResult();
            $max = $query
                ->orderBy('v.vote', 'DESC')
                ->getQuery()
                ->getResult();
            foreach ($max as &$id)
                array_push($cutVotes, $id['id']);
            foreach ($min as &$id)
                array_push($cutVotes, $id['id']);
            $votesLeft = $query
                ->andWhere('v.id NOT IN (:cut)')
                ->setParameter('cut', $cutVotes)
                ->setMaxResults(null)
                ->getQuery()
                ->getResult();
            $opiekunVoting->setScore(array_sum(array_column($votesLeft, 'vote')));
        }
        $results = $opiekunVotings->toArray();
        usort($results, function($a, $b) {
            return $a->getScore() < $b->getScore() ? 1 : -1;
        });
        return $results;
    }

    public function getPnapnPeopleVoted($markerVoting){
        $votes = $this->findBy(['Voting' => $markerVoting, 'vote' => 99]);
        $peopleVoted = [];
        foreach ($votes as &$vote)
            array_push($peopleVoted, $vote->getRoleValidityId());
        return $peopleVoted;
    }

    public function getPnapnPeopleSaved($markerVoting){
        $votes = $this->findBy(['Voting' => $markerVoting, 'vote' => 55]);
        $peopleSaved = [];
        foreach ($votes as &$vote)
            array_push($peopleSaved, $vote->getRoleValidityId());
        return $peopleSaved;
    }

    // /**
    //  * @return Vote[] Returns an array of Vote objects
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
    public function findOneBySomeField($value): ?Vote
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
