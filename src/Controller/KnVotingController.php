<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\ResearchGroup;
use App\Entity\VotingGroup;
use App\Entity\Voting;
use App\Entity\Vote;
use App\Entity\MeetingAttendance;

use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Form\VotingType;

class KnVotingController extends AbstractController
{
    /**
     * @Route("/kn/meetings/{meetingId}/votings/", name="kn_votings_list")
     * Method({"GET",})
     */
    public function votingsList($meetingId)
    {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($meetingId);
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_W_'.$knOlimpId) &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_ST_'.$knOlimpId))
            return $this->render('notallowed.html.twig');
        else {
            $votingGroups = $this->getDoctrine()->getRepository(VotingGroup::class)
                ->findBy(
                    ['Meeting' => $meeting, 'deleted' => false],
                    ['id' => 'ASC']
                );

            return $this->render('knMeetings/voting_list.html.twig', [
                'meeting' => $meeting,
                'kn' => $kn,
                'votingGroups' => $votingGroups
            ]);
        }
    }

    /**
     * @Route("/kn/meetings/{meetingId}/votings/new", name="kn_add_voting")
     * Method({"GET", "POST"})
     */
    public function addVoting(Request $request, $meetingId)
    {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($meetingId);
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CHAIRMAN_'.$knOlimpId))
            return $this->render('notallowed.html.twig');
        else {
            $item = new VotingGroup();
            $item->setDeleted(false);

            $form = $this->createFormBuilder($item)
                ->add('subject', TextType::class, [
                    'label' => 'Tytuł głosowania',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('maxVotesFor', IntegerType::class, [
                    'label' => 'Liczba miejsc do obsadzenia (gdy są głosowania składowe)',
                    'data' => 1,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('votings', CollectionType::class, [
                    'entry_type' => VotingType::class,
                    'label' => false,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                ])
                ->add('save', SubmitType::class, array(
                    'label' => 'Utwórz',
                    'attr' => array('class' => 'btn btn-primary mt-3'),
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $item = $form->getData();
                $item->setMeeting($meeting);
                $votings = $item->getVotings();
                if ($votings->isEmpty()) {
                    $voting = new Voting();
                    $voting->setSubject($item->getSubject());
                    $voting->setVotingGroup($item);
                    $votings->add($voting);
                } else foreach ($votings as &$voting) {
                    $voting->setVotingGroup($item);
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($item);
                $entityManager->flush();
                return $this->redirectToRoute('kn_votings_list', array('meetingId' => $meetingId));
            }

            $knNameD = $kn->getNameD()?$kn->getNameD():$kn->getName();
            return $this->render('knMeetings/voting_add.html.twig', array(
                'kn' => $kn,
                'form' => $form->createView(),
                'title' => 'Nowe głosowanie na zebraniu '.$knNameD
            ));
        }
    }

    /**
     * @Route("/kn/meetings/votings/{votingGroupId}/edit", name="kn_edit_voting")
     * Method({"GET", "POST"})
     */
    public function editVoting(Request $request, $votingGroupId)
    {
        $votingGroup = $this->getDoctrine()->getRepository(VotingGroup::class)->find($votingGroupId);
        $meeting = $votingGroup->getMeeting();
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CHAIRMAN_' . $knOlimpId))
            return $this->render('notallowed.html.twig');
        else {
            //remove voting at first so that the candidate field is not shown when not necessary
            if($votingGroup->getVotings()->count() == 1){
                $voting = $votingGroup->getVotings()[0];
                $votingGroup->removeVoting($voting);
            }

            $form = $this->createFormBuilder($votingGroup)
                ->add('subject', TextType::class, [
                    'label' => 'Tytuł głosowania',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('maxVotesFor', IntegerType::class, [
                    'label' => 'Liczba miejsc do obsadzenia (gdy są głosowania składowe)',
                    'data' => 1,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('votings', CollectionType::class, [
                    'entry_type' => VotingType::class,
                    'label' => false,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                ])
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować? ',
                    'choices' => array(
                        'Nie' => false,
                        'Tak' => true,
                    ),
                    'data' => false,
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zapisz',
                    'attr' => array('class' => 'btn btn-primary mt-3'),
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $item = $form->getData();
                $votings = $item->getVotings();
                if ($votings->isEmpty()) {
                    $voting = new Voting();
                    $voting->setSubject($item->getSubject());
                    $voting->setVotingGroup($item);
                    $votings->add($voting);
                } else foreach ($votings as &$voting) {
                    $voting->setVotingGroup($item);
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('kn_votings_list', array('meetingId' => $meeting->getId()));
            }

            $knNameD = $kn->getNameD()?$kn->getNameD():$kn->getName();
            return $this->render('knMeetings/voting_add.html.twig', array(
                'kn' => $kn,
                'form' => $form->createView(),
                'title' => 'Edytuj głosowanie na zebraniu '.$knNameD
            ));
        }
    }

    /**
    //     * @Route("/kn/meetings/votings/{votingGroupId}", name="view_kn_voting")
    //     * Method({"GET", "POST"})
    //     */
    public function view_voting(Request $request, $votingGroupId, Connection $connection){
        $votingGroup = $this->getDoctrine()->getRepository(VotingGroup::class)->find($votingGroupId);
        $meeting = $votingGroup->getMeeting();
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_W_'.$knOlimpId) &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_ST_'.$knOlimpId))
            return $this->render('notallowed.html.twig');
        else {
            $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getPeoplePresent($meeting);

            if (!$votingGroup->getStarted())
                return $this->votingNotStarted($request, $kn, $meeting, $votingGroup);
            else if (!$votingGroup->getEnded())
                return $this->votingInProgress($request, $kn, $meeting, $votingGroup, $connection);
            else return $this->render('knMeetings/voting_results.html.twig', [
                    'kn' => $kn,
                    'meeting' => $meeting,
                    'votingGroup' => $votingGroup,
                    'attendance' => $attendance
                ]);
        }
    }

    public function votingNotStarted(Request $request, $kn, $meeting, $votingGroup){
        $form = $this->createFormBuilder($votingGroup)
            ->add('start', SubmitType::class, array(
                'label' => 'Rozpocznij głosowanie',
                'attr' => array('class' => 'btn btn-primary mt-3'),
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $votingGroup->setStarted(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('view_kn_voting', ['votingGroupId' => $votingGroup->getId()]);
        }
        return $this->render('knMeetings/voting_not_started.html.twig', array(
            'kn' => $kn,
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'startBtn' => $form->createView()
        ));
    }

    public function votingInProgress(Request $request, $kn, $meeting, $votingGroup, Connection $connection){
        $knOlimpId = $kn->getOlimpId();
        $membersPresent = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getMembersPresent($meeting, $connection, $knOlimpId);
        $peopleVoted = $this->getDoctrine()->getRepository(VotingGroup::class)->getPeopleVoted($votingGroup);
        //get user id and check status
        if ($this->getUser()) {
            $userUID = $this->getUser()->getOlimpID();
            $rvid = $connection->fetchColumn("SELECT validity_id as rvid FROM mainframe.vroles_active
                WHERE org_id = '$knOlimpId' AND role_type = 'W' AND uid = '$userUID'");
            $hasAlreadyVoted = in_array($rvid, $peopleVoted);
            $hasVotingRight = in_array($rvid, $membersPresent);
        }
        else $hasVotingRight = $hasAlreadyVoted = false;

        return
            $hasVotingRight ?
                $hasAlreadyVoted ?
                    $this->alreadyVoted($request, $kn, $meeting, $votingGroup, $membersPresent)
                : $this->votingForm($request, $kn, $meeting, $votingGroup, $rvid)
            : $this->render('meetings/vote_not_allowed.html.twig', [
                'meeting' => $meeting,
                'votingGroup' => $votingGroup,
                'kn' => $kn
            ]);
    }

    public function alreadyVoted($request, $kn, $meeting, $votingGroup, $attendance){
        $form = $this->createFormBuilder($votingGroup)
            ->add('end', SubmitType::class, array(
                'label' => 'Zakończ głosowanie',
                'attr' => array('class' => 'btn btn-primary mt-3'),
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $votingGroup->setEnded(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('view_kn_voting', ['votingGroupId' => $votingGroup->getId()]);
        }

        return $this->render('knMeetings/already_voted.html.twig', [
            'kn' => $kn,
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'attendance' => $attendance,
            'endBtn' => $form->createView()
        ]);
    }

    public function votingForm(Request $request, $kn, $meeting, $votingGroup, $rvid){
        $builder = $this->createFormBuilder($votingGroup);
        foreach ($votingGroup->getVotings() as &$voting){
            $builder->add('voting'.$voting->getId(), ChoiceType::class, array(
                'mapped' => false,
                'attr' => array('class' => 'form-control'),
                'label' => $voting->getSubject(),
                'choices' => [
                    'Za' => '1',
                    'Przeciw' => '2',
                    'Wstrzymuję się' => '3',
                ],
            ));
        }
        $builder->add('submit', SubmitType::class, array(
            'attr' => array('class' => 'btn btn-primary mt-3'),
            'label' => 'Zagłosuj',
        ));
        $voteForm = $builder->getForm();
        $voteForm->handleRequest($request);
        if ($voteForm->isSubmitted() and $voteForm->isValid()) {
            $votesFor = 0;
            if (count($votingGroup->getVotings()) > 1) {
                //if group has more than one voting, check number of votes for
                foreach ($votingGroup->getVotings() as &$voting) {
                    $voteVal = $voteForm->get("voting" . $voting->getID())->getData();
                    if ($voteVal == 1)
                        $votesFor += 1;
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($votingGroup->getVotings() as &$voting) {
                $vote = new Vote();
                $vote->setVoting($voting);
                $vote->setRoleValidityID($rvid);
                //if voting has max votes for limit, make particular votes invalid
                if (count($votingGroup->getVotings()) > 1 && $votesFor > $votingGroup->getMaxVotesFor())
                    $vote->setVote(null);
                else $vote->setVote($voteForm->get("voting" . $voting->getID())->getData());
                $entityManager->persist($vote);
                $entityManager->flush();
            }
            return $this->redirectToRoute('view_kn_voting', ['votingGroupId' => $votingGroup->getId()]);
        }
        return $this->render('meetings/voting_in_progress.html.twig', [
            'kn' => $kn,
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'voteForm' => $voteForm->createView()
        ]);
    }
}