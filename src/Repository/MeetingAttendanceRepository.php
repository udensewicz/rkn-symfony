<?php

namespace App\Repository;

use App\Entity\MeetingAttendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MeetingAttendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingAttendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingAttendance[]    findAll()
 * @method MeetingAttendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MeetingAttendance::class);
    }

    public function getPeoplePresent($meeting)
    {
        $attendances = $this->findBy(['Meeting' => $meeting, 'present' => true]);
        $peoplePresent = [];
        foreach ($attendances as &$att)
            array_push($peoplePresent, $att->getRoleValidityId());
        return $peoplePresent;
    }

    public function getMembersPresent($meeting, $connection, $knOlimpId)
    {
        $attendances = $this->findBy(['Meeting' => $meeting, 'present' => true]);
        $members = $connection->fetchAll("SELECT validity_id as rvid FROM mainframe.vroles_active
                WHERE org_id = '$knOlimpId' AND role_type = 'W'");
        $membersPresent = [];
        foreach ($attendances as &$att)
            if (in_array($att->getRoleValidityId(), array_column($members, 'rvid')))
                array_push($membersPresent, $att->getRoleValidityId());
        return $membersPresent;
    }

    // /**
    //  * @return MeetingAttendance[] Returns an array of MeetingAttendance objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MeetingAttendance
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
