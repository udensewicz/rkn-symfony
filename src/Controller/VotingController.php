<?php

namespace App\Controller;

use App\Entity\Meeting;
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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Form\VotingType;

class VotingController extends AbstractController
{
    /**
    * @Route("/meetings/{id}/votings/", name="votings_list")
    * Method({"GET",})
    */
    public function votings_list($id)
    {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);
        $votingGroups = $this->getDoctrine()->getRepository(VotingGroup::class)
            ->findBy(
                ['Meeting' => $meeting, 'deleted' => false],
                ['id' => 'ASC']
            );

        return $this->render('meetings/voting_list.html.twig', [
            'meeting' => $meeting,
            'votingGroups' => $votingGroups
        ]);
    }

    /**
    //     * @Route("/meetings/{id}/votings/new", name="new_voting")
    //     * Method({"GET", "POST"})
    //     */
    public function new_voting(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new VotingGroup();
            $item->setDeleted(false);
            $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);

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
                ->add('isBestOpiekun', CheckboxType::class, [
                    'label' => 'Głosowanie na najlepszego opiekuna? (skala 1-10)',
                    'required' => false,
                    'attr' => ['class' => 'form-check']
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
                return $this->redirectToRoute('votings_list', array('id' => $id));
            }

            return $this->render('meetings/voting_add.html.twig', array(
                'form' => $form->createView()
            ));
        }
    }

    /**
    //     * @Route("/meetings/votings/{votingGroupId}", name="view_voting")
    //     * Method({"GET", "POST"})
    //     */
    public function view_voting(Request $request, $votingGroupId, Connection $connection){
        $votingGroup = $this->getDoctrine()->getRepository(VotingGroup::class)->find($votingGroupId);
        $meeting = $votingGroup->getMeeting();

        if (!$votingGroup->getStarted())
            return $this->votingNotStarted($request, $meeting, $votingGroup);
        else if (!$votingGroup->getEnded())
            return $this->votingInProgress($request, $meeting, $votingGroup, $connection);
        else return $this->votingResults($meeting, $votingGroup);
    }

    public function votingNotStarted(Request $request, $meeting, $votingGroup){
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
            return $this->redirectToRoute('view_voting', ['votingGroupId' => $votingGroup->getId()]);
        }
        return $this->render('meetings/voting_not_started.html.twig', array(
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'startBtn' => $form->createView()
        ));
    }

    public function votingInProgress(Request $request, $meeting, $votingGroup, Connection $connection){
        $peoplePresent = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getPeoplePresent($meeting);
        $peopleVoted = $this->getDoctrine()->getRepository(VotingGroup::class)->getPeopleVoted($votingGroup);
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
                    $this->alreadyVoted($request, $meeting, $votingGroup, $peoplePresent)
                : $this->votingForm($request, $meeting, $votingGroup, $rvid)
            : $this->render('meetings/vote_not_allowed.html.twig', ['meeting' => $meeting, 'votingGroup' => $votingGroup]);
    }

    public function alreadyVoted($request, $meeting, $votingGroup, $attendance){
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
            return $this->redirectToRoute('view_voting', ['votingGroupId' => $votingGroup->getId()]);
        }

        return $this->render('meetings/already_voted.html.twig', [
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'attendance' => $attendance,
            'endBtn' => $form->createView()
        ]);
    }

    public function votingForm(Request $request, $meeting, $votingGroup, $rvid){
        $builder = $this->createFormBuilder($votingGroup);
        foreach ($votingGroup->getVotings() as &$voting){
            $builder->add('voting'.$voting->getId(), ChoiceType::class, array(
                'mapped' => false,
                'attr' => array('class' => 'form-control'),
                'label' => $voting->getSubject(),
                'choices' => $votingGroup->getIsBestOpiekun() ?
                    ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10]
                    :
                    ['Za' => '1',
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
            $votesFor = [];
            if (count($votingGroup->getVotings()) > 1 && !$votingGroup->getIsBestOpiekun()) {
                //if group has more than one voting and is not opiekun voting, check number of votes for
                foreach ($votingGroup->getVotings() as &$voting) {
                    $voteVal = $voteForm->get("voting" . $voting->getID())->getData();
                    if ($voteVal == 1)
                        array_push($votesFor, $voteVal);
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($votingGroup->getVotings() as &$voting) {
                $vote = new Vote();
                $vote->setVoting($voting);
                $vote->setRoleValidityID($rvid);
                //if voting has max votes for limit, make votes exceeding that limit invalid
                if (count($votingGroup->getVotings()) > 1 && !$votingGroup->getIsBestOpiekun()
                    && count($votesFor) > $votingGroup->getMaxVotesFor())
                    $vote->setVote(null);
                else $vote->setVote($voteForm->get("voting" . $voting->getID())->getData());
                $entityManager->persist($vote);
                $entityManager->flush();
            }
            return $this->redirectToRoute('view_voting', ['votingGroupId' => $votingGroup->getId()]);
        }
        return $this->render('meetings/voting_in_progress.html.twig', [
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'voteForm' => $voteForm->createView()
        ]);
    }

    public function votingResults($meeting, $votingGroup){
        $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)->getPeoplePresent($meeting);

        if ($votingGroup->getIsBestOpiekun()) {
            $repository = $this->getDoctrine()->getRepository(Vote::class);
            $peopleVoted = $repository->getPeopleVoted($votingGroup);
            $cut = ceil( count($peopleVoted) / 20);
            $results = $repository->getOpiekunScores($votingGroup, $cut);
            return $this->render('meetings/voting_opiekun_results.html.twig', [
                'meeting' => $meeting,
                'votingGroup' => $votingGroup,
                'attendance' => $attendance,
                'results' => $results,
            ]);
        }

        else return $this->render('meetings/voting_results.html.twig', [
            'meeting' => $meeting,
            'votingGroup' => $votingGroup,
            'attendance' => $attendance
        ]);
    }

    /**
    //     * @Route("/meetings/votings/{votingGroupId}/edit", name="edit_voting")
    //     * Method({"GET", "POST"})
    //     */
    public function edit_voting(Request $request, $votingGroupId){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $votingGroup = $this->getDoctrine()->getRepository(VotingGroup::class)->find($votingGroupId);

            //the remove voting is so that the candidate field is not shown when not necessary
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
                    'label' => 'Liczba miejsc do obsadzenia (gdy ma głosowania składowe)',
                    'attr' => ['class' => 'form-control']
                ])
                ->add('isBestOpiekun', CheckboxType::class, [
                    'label' => 'Głosowanie na najlepszego opiekuna? (skala 1-10)',
                    'required' => false,
                    'attr' => ['class' => 'form-check']
                ])
                ->add('votings', CollectionType::class, [
                    'entry_type' => VotingType::class,
                    'label' => false,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
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
                return $this->redirectToRoute('votings_list', array('id' => $votingGroup->getMeeting()->getId()));
            }

            return $this->render('meetings/voting_add.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj głosowanie',
            ));
        }
    }

    //DATA MIGRATION FUNCTION!

//    /**
//     * @Route("/copymeeting", name="user_view")
//     */
//    public function copy(Connection $connection): Response
//    {
//        $data = $connection->fetchAll("SELECT v.voting_id, v.role_validity_id, v.vote, m.subject FROM rkn.voting_votes v
//            LEFT JOIN rkn.voting m ON m.id = v.voting_id
//            WHERE m.id IS NOT NULL AND role_validity_id IS NOT NULL
//    	");
//        if ($data != NULL) {
//            foreach ($data as &$item) {
//                $vote = new Vote();
//                $votingTitle = $item['subject'];
//                $voting = $this->getDoctrine()->getRepository(Voting::class)->findOneBy(['subject' => $votingTitle]);
//                $vote->setVoting($voting);
//                $vote->setRoleValidityId($item['role_validity_id']);
//                $vote->setVote($item['vote']);
//                $entityManager = $this->getDoctrine()->getManager();
//                $entityManager->persist($vote);
//                $entityManager->flush();
//            }
//        }
//        return $this->redirectToRoute('meetings');
//    }
}