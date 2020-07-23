<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingAttendance;
use App\Entity\PnapnVoting;
use App\Entity\PnapnProject;
use App\Entity\PnapnVote;
use App\Entity\Vote;
use App\Entity\Voting;

use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use AppBundle\Form\FormValidationType;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\PnapnVoteType;
use App\Form\PnapnProjectType;
use App\Form\ProjectListType;

class PnapnController extends AbstractController
{
    /**
     * @Route("/meetings/pnapn", name="pnapn_meetings")
     * Method({"GET", "POST"})
     */
    public function meetingsList(Request $request, Connection $connection)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin')) {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('published' => true, 'deleted' => false, 'havePnapn' => true, 'researchGroup' => null),
                    array('dateStart' => 'DESC')
                );
        } else {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('deleted' => false, 'havePnapn' => true, 'researchGroup' => null),
                    array('dateStart' => 'DESC')
                );
        }
        $rknStatuses = [
            "RKN_PRESENTATION_OK" => "Dostarczono prezentację",
            "WAITING_RKN" => "Oczekuje",
            "WAITING" => "Oczekuje",
            "RKN_SAD_REJECT" => "Zgłoszenie odrzucono",
            "RKN_CHICKEN_OUT" => "Koło wycofało się",
            'WAITING_RKN_GET_CASH' => 'Projekt otrzymał środki',
            'WAITING_RKN_DONT_GET_CASH' => 'Projekt nie otrzymał środków'
        ];
        $meetingObjectList = [];
        //a MeetingObject consists of three things, a Meeting class instance with meeting data, a Projects array of
        //projects for this meeting, in correct order,  and a form where you can edit projects data e.g status, money min amount etc.
        foreach ($meetings as &$meeting) {
            $projects = $this->get('security.authorization_checker')->isGranted('ROLE_site_admin') ?
                    $this->getDoctrine()->getRepository(PnapnProject::class)->listAllMeetingProjects($meeting->getPnapnVoting())
                : $this->getDoctrine()->getRepository(PnapnProject::class)->listAllowedMeetingProjects($meeting->getPnapnVoting());
            $form = $this->get('form.factory')->createNamed('meeting' . $meeting->getId(), ProjectListType::class, ['projects' => $projects]);
            $meetingObject['meeting'] = $meeting;
            $meetingObject['projects'] = $projects;
            $meetingObject['form'] = $form->createView();
            array_push($meetingObjectList, $meetingObject);
            if ($request->request->has('meeting' . $meeting->getId())) {
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();
                    //update project data to olimp
                    foreach ($data['projects'] as &$project){
                        $id = $project->getProjectID();
                        $min = $project->getMoneyMinAmount() ? $project->getMoneyMinAmount() : 0;
                        $max = $project->getMoneyMaxAmount() ? $project->getMoneyMaxAmount() : 0;
                        $status = $project->getStatus();
                        $connection->executeUpdate("UPDATE rkn.pnapn_projects
                          SET min='$min', max='$max', status='$status'
                          WHERE projekt_id='$id'");
                    }
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();
                    return $this->redirectToRoute('pnapn_meetings');
                }
            }
        }

        return $this->render('pnapn/meetings.html.twig', array(
            'meetings' => $meetingObjectList,
            'statuses' => $rknStatuses,
        ));
    }

    /**
     * @Route("/meetings/pnapn/{id}/edit", name="edit_pnapn_voting")
     * Method({"GET", "POST"})
     */
    public function editMeeting(Request $request, $id, Connection $connection)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);
            $pnapn = $meeting->getPnapnVoting();
            $form = $this->createFormBuilder($pnapn)
                ->add('applyingFrom', DateTimeType::class, array(
                    'label' => 'Składanie wniosków od',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('applyingTo', DateTimeType::class, array(
                    'label' => 'Składanie wniosków do',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('type', ChoiceType::class, array(
                    'attr' => array('class' => 'form-control'),
                    'label' => 'Rodzaj puli',
                    'choices' => array(
                        'Duża pula' => 'd',
                        'Mała pula' => 'm',
                        'Rezerwowa pula' => 'r',
                    )
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zapisz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $item = $form->getData();
                $olimpId = $meeting->getOlimpId();
                $stmt = $connection->prepare("UPDATE rkn.pnapn_list
                        SET applying_from = ?, applying_to = ?, pnapn_type = ?
                        WHERE id = '$olimpId'");
                $stmt->bindValue(1, $item->getApplyingFrom(), "datetime");
                $stmt->bindValue(2, $item->getApplyingTo(), "datetime");
                $stmt->bindValue(3, $item->getType());
                $stmt->execute();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('pnapn_meetings');
            }

            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => $meeting->getTitle(),
            ));
        }
    }


    /**
    //     * @Route("/meetings/pnapn/{id}/voting", name="view_pnapn_voting")
    //     * Method({"GET", "POST"})
    //     */
    public function viewVoting(Request $request, $id, Connection $connection){
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);
        $voting = $meeting->getPnapnVoting();

        if (!$voting->getDateStarted())
            return $this->votingNotStarted($request, $meeting);
        else if (!$voting->getDateEnded())
            return $this->votingInProgress($request, $meeting, $connection);
        else return $this->votingResults($meeting);
    }

    public function votingNotStarted($request, $meeting) {
        $pnapn = $meeting->getPnapnVoting();
        $form = $this->createFormBuilder()
            ->add('start', SubmitType::class, array(
                'label' => 'Rozpocznij głosowanie',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pnapn->setDateStarted(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('view_pnapn_voting', ['id' => $meeting->getId()]);
        }
        return $this->render('pnapn/voting_not_started.html.twig', [
            'startBtn' => $form->createView(),
            'meeting' => $meeting,
        ]);
    }

    public function votingInProgress($request, $meeting, Connection $connection) {
        $markerVoting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy(['Meeting' => $meeting]);
        $peopleVoted = $this->getDoctrine()->getRepository(Vote::class)->getPnapnPeopleVoted($markerVoting);
        $peoplePresent = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getPeoplePresent($meeting);
        //get user id and check status
        if ($this->getUser()) {
            $userUID = $this->getUser()->getOlimpID();
            $rvid = $connection->fetchColumn("SELECT validity_id as rvid FROM mainframe.vroles_active
                WHERE org_id = 54223 AND role_type = 'W' AND uid = '$userUID'");
            $hasAlreadyVoted = in_array($rvid, $peopleVoted);
            $hasVotingRight = in_array($rvid, $peoplePresent);
        }
        else $hasVotingRight = $hasAlreadyVoted = false;

        return
            $hasVotingRight ?
                $hasAlreadyVoted ?
                    $this->alreadyVoted($request, $meeting, $peopleVoted, $peoplePresent)
                : $this->votingForm($request, $meeting, $rvid)
            : $this->render('pnapn/not_allowed.html.twig', ['meeting' => $meeting]);
    }

    public function alreadyVoted($request, $meeting, $peopleVoted, $peoplePresent){
        $pnapn = $meeting->getPnapnVoting();
        $markerVoting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy(['Meeting' => $meeting]);
        $peopleInProgress = $this->getDoctrine()->getRepository(Vote::class)->getPnapnPeopleSaved($markerVoting);
        $peopleNotStarted = array_diff($peoplePresent, array_merge($peopleInProgress, $peopleVoted));

        //end voting button
        $form = $this->createFormBuilder()
            ->add('end', SubmitType::class, array(
                'label' => 'Zakończ głosowanie',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pnapn->setDateEnded(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('view_pnapn_voting', ['id' => $meeting->getId()]);
        }
        return $this->render('pnapn/voting_already_voted.html.twig', [
            'endBtn' => $form->createView(),
            'meeting' => $meeting,
            'allowed' => count($peoplePresent),
            'finished' => count($peopleVoted),
            'inprogress' => count($peopleInProgress),
            'notstarted' => count($peopleNotStarted)
        ]);
    }

    public function votingForm($request, $meeting, $rvid) {
        $projects = $this->getDoctrine()->getRepository(PnapnProject::class)->findBy([
            'Voting' => $meeting->getPnapnVoting(),
            'status' => 'RKN_PRESENTATION_OK'],
            ['presentationOrder' => 'ASC']
        );
        $markerVoting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy(['Meeting' => $meeting]);
        $votes = [];
        foreach ($projects as &$project) {
            $vote = $this->getDoctrine()->getRepository(PnapnVote::class)->findOneBy([
                'Meeting' => $meeting,
                'Project' => $project,
                'roleValidityId' => $rvid
            ]);
            if (!$vote) {
                $vote = new PnapnVote();
                $vote->setProject($project);
                $vote->setMeeting($meeting);
                $vote->setRoleValidityId($rvid);
            }
            array_push($votes, $vote);
        }
        $form = $this->createFormBuilder(['votes' => $votes])
            ->add('votes', CollectionType::class, [
                'entry_type' => PnapnVoteType::class,
                'label' => false,
                'entry_options' => ['label' => false],
            ])
            ->add('save', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary mt-3'),
                'label' => 'Zapisz głos',
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary mt-3'),
                'label' => 'Zapisz i wrzuć do urny',
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $votes = $form->getData()['votes'];
            $markerVote = $this->getDoctrine()->getRepository(Vote::class)->findOneBy([
                'Voting' => $markerVoting,
                'roleValidityId' => $rvid
            ]);
            if (!$markerVote) {
                $markerVote = new Vote();
                $markerVote->setRoleValidityId($rvid);
                $markerVote->setVoting($markerVoting);
            }
            $voteStatus = $form->get('save')->isClicked() ? 55 : 99;
            $markerVote->SetVote($voteStatus);
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($votes as &$vote)
                $entityManager->persist($vote);
            $entityManager->persist($markerVote);
            $entityManager->flush();
            return $this->redirectToRoute('view_pnapn_voting', array(
                'id' => $meeting->getId(),
            ));
        }
        return $this->render('pnapn/voting_in_progress.html.twig', [
            'meeting' => $meeting,
            'projects' => $projects,
            'form' => $form->createView(),
        ]);
    }

    public function votingResults($meeting) {
        $repository = $this->getDoctrine()->getRepository(PnapnVote::class);
        $peopleVoted = $repository->getPeopleVoted($meeting);
        $peoplePresent = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getPeoplePresent($meeting);
        $markerVoting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy(['Meeting' => $meeting]);
        $invalidVoters = $repository->getInvalidVoters($meeting, $markerVoting);
        $validVoters = array_diff($peopleVoted, $invalidVoters);
        $cut = ceil( count($validVoters) / 10);
        $results = $repository->getProjectScores($meeting, $validVoters, $cut);
//        dd($invalidVoters);
        return $this->render('pnapn/voting_results.html.twig', [
            'meeting' => $meeting,
            'attendance' => $peoplePresent,
            'voters' => $peopleVoted,
            'invalid' => $invalidVoters,
            'valid' => $validVoters,
            'cut' => $cut,
            'results' => $results
        ]);
    }

//    /**
//    //     * @Route("/copymeeting", name="user_view")
//    //     */
//    public function copy(Connection $connection): Response
//    {
//        $data = $connection->fetchAll("SELECT * from rkn.voting_votes v
//            LEFT JOIN rkn.pnapn_list l ON l.id = v.voting_id
//            WHERE v.vote='99' AND l.posiedzenie_id = 'II'
//    	");
////        dd($data);
//        if ($data != NULL) {
//            $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find(16);
//            $voting = new Voting();
//            $voting -> setSubject('II Posiedzenie oceniające Mała Pula na Projekty Naukowe Rady Kół Naukowych 2018');
//            $voting -> setMeeting($meeting);
//            $entityManager = $this->getDoctrine()->getManager();
//            $entityManager->persist($voting);
//            $entityManager->flush();
//            foreach ($data as &$item) {
//                $vote = new Vote();
//                $vote -> setVoting($voting);
//                $vote -> setVote($item['vote']);
//                $vote -> setRoleValidityId($item['role_validity_id']);
//                $entityManager->persist($vote);
//                $entityManager->flush();
//
//            }
//        }
//        return $this->redirectToRoute('meetings');
//    }
}