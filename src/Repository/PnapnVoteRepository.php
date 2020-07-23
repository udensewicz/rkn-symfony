<?php

namespace App\Repository;

use App\Entity\PnapnVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PnapnVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PnapnVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PnapnVote[]    findAll()
 * @method PnapnVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PnapnVoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PnapnVote::class);
    }

    public function getUserVotes($rvid, $meeting)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.roleValidityId = :val')
            ->setParameter('val', $rvid)
            ->andWhere('p.Meeting = :meeting')
            ->setParameter('meeting', $meeting)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getPeopleVoted($meeting)
    {
        $votes = $this->findBy(['Meeting' => $meeting]);
        $peopleVoted = [];
        foreach ($votes as &$vote)
            if (!in_array($vote->getRoleValidityID(), $peopleVoted)) {
                array_push($peopleVoted, $vote->getRoleValidityId());
            }
        return $peopleVoted;
    }

    public function getInvalidVoters($meeting, $markerVoting){
        $votes = $this->findBy(['Meeting' => $meeting]);
        $invalidRvids = [];
        foreach ($votes as &$vote)
            if (!in_array($vote->getRoleValidityID(), $invalidRvids) && !$vote->isValid()) {
                array_push($invalidRvids, $vote->getRoleValidityId());
            }
        //marker votes are the one that keep track of whether vote is saved or in urn
        $markerVotes = $markerVoting->getVotes();
        foreach ($markerVotes as $vote)
            if (!in_array($vote->getRoleValidityID(), $invalidRvids) && $vote->getVote() != 99) {
                array_push($invalidRvids, $vote->getRoleValidityId());
            }
        return $invalidRvids;
    }

    public function getProjectScores($meeting, $validVoters, $cut){
        $projects = $meeting->getPnapnVoting()->getPnapnProjects();
        foreach ($projects as &$project){
            $cutVotes = [];
            $query = $this->createQueryBuilder('v')
                ->andWhere('v.Project = :val')
                ->setParameter('val', $project)
                ->andWhere('v.roleValidityId IN (:validvoters)')
                ->setParameter('validvoters', $validVoters)
                ->select('v.id, v.voteCat1*0.35+v.voteCat2*0.2+v.voteCat3*0.2+v.voteCat4*0.15+v.voteCat5*0.1 AS votesum');
            $min = $query
                ->orderBy('votesum', 'ASC')
                ->setMaxResults($cut)
                ->getQuery()
                ->getResult();
            $max = $query
                ->orderBy('votesum', 'DESC')
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
            $project->setScore(array_sum(array_column($votesLeft, 'votesum')));
        }
        $results = $projects->toArray();
        usort($results, function($a, $b) {
            return $a->getScore() < $b->getScore() ? 1 : -1;
        });
        return $results;
    }

    // /**
    //  * @return PnapnVote[] Returns an array of PnapnVote objects
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
    public function findOneBySomeField($value): ?PnapnVote
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
