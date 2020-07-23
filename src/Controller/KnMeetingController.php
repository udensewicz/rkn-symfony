<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\ResearchGroup;
use App\Entity\MeetingAttendance;
use App\Entity\User;
//use App\Entity\Voting;

use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\FormValidationType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class KnMeetingController extends AbstractController
{
    /**
     * @Route("/kn/{id}/meetings/all", name="kn_meetings")
     * Method({"GET",})
     */
    public function meetingsList($id)
    {
        $kn = $picture = $this->getDoctrine()->getRepository(ResearchGroup::class)->find($id);
        $knId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_W_'.$knId) &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_ST_'.$knId))
            return $this->render('notallowed.html.twig');
        else if (!$this->get('security.authorization_checker')->isGranted('ROLE_CHAIRMAN_'.$knId)) {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('published' => true, 'deleted' => false, 'researchGroup' => $kn),
                    array('dateStart' => 'DESC')
                );
        } else {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('deleted' => false, 'researchGroup' => $kn),
                    array('dateStart' => 'DESC')
                );
        }
        return $this->render('knMeetings/list.html.twig', array(
            'meetings' => $meetings,
            'kn' => $kn
        ));
    }

    /**
    //     * @Route("/kn/{id}/meetings/new", name="new_kn_meeting")
    //     * Method({"GET", "POST"})
    //     */
    public function newMeeting(Request $request, $id){
        $kn = $picture = $this->getDoctrine()->getRepository(ResearchGroup::class)->find($id);
        $knId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CHAIRMAN_'.$knId))
            return $this->render('notallowed.html.twig');
        else {
            $item = new Meeting();
            $item->setDeleted(false);
            $item->setHavePnapn(false);
            $item->setResearchGroup($kn);
            $user = $this->getUser();
            $form = $this->createFormBuilder($item)
                ->add('title', TextType::class, array(
                    'label' => 'Nazwa zebrania',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('published', CheckboxType::class, array(
                    'label' => 'Opublikowane?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
                ))
                ->add('plan', TextareaType::class, array(
                    'label' => 'Plan zebrania',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('date_start', DateTimeType::class, array(
                    'label' => 'Data rozpoczęcia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('date_end', DateTimeType::class, array(
                    'label' => 'Data zakończenia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Utwórz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $item = $form->getData();
                $item->setCreatedBy($user);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($item);
                $entityManager->flush();

                return $this->redirectToRoute('kn_meetings', array('id' => $id));
            }

            $knNameD = $kn->getNameD()?$kn->getNameD():$kn->getName();
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowe zebranie '.$knNameD,
                'textareaheight' => 200,
            ));
        }
    }

    /**
     * @Route("/kn/meetings/{meetingId}/edit", name="edit_kn_meeting")
     * Method({"GET", "POST"})
     */
    public function editMeeting(Request $request, $meetingId)
    {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($meetingId);
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CHAIRMAN_'.$knOlimpId))
            return $this->render('notallowed.html.twig');
        else {
            $form = $this->createFormBuilder($meeting)
                ->add('title', TextType::class, array(
                    'label' => 'Nazwa zebrania',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('published', CheckboxType::class, array(
                    'label' => 'Opublikowane?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
                ))
                ->add('plan', TextareaType::class, array(
                    'label' => 'Plan zebrania',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('date_start', DateTimeType::class, array(
                    'label' => 'Data rozpoczęcia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('date_end', DateTimeType::class, array(
                    'label' => 'Data zakończenia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować zebranie?',
                    'choices' => array(
                        'Nie' => false,
                        'Tak' => true,
                    ),
                    'data' => false,
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zapisz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('kn_meetings', array('id' => $kn->getId()));
            }

            $knNameD = $kn->getNameD()?$kn->getNameD():$kn->getName();
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj zebranie '.$knNameD,
                'textareaheight' => 200,
            ));
        }
    }

    /**
     * @Route("/kn/meetings/{meetingId}/attendance", name="edit_kn_attendance")
     * Method({"GET", "POST"})
     */
    public function attendanceView (Request $request, $meetingId, Connection $connection) {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($meetingId);
        $kn = $meeting->getResearchGroup();
        $knOlimpId = $kn->getOlimpID();

        $dateStart = $meeting->getDateStart()->format('Y-m-d H:i:s');
        $dateEnd = $meeting->getDateEnd()->format('Y-m-d H:i:s');
        //wycianij z olimpa wszystkich aktywnych czlonkow na czas posiedzenia
        $members = $connection->fetchAll("SELECT u.name, u.surname, r.role_type, r.rvid, CASE r.role_type WHEN 'W' THEN 1 ELSE 2 END AS OrderBy
            FROM mainframe.f_roles_active_for_date('$dateStart', '$dateEnd') r
            LEFT JOIN mainframe.login u ON u.id = r.uid
            WHERE r.org_id = '$knOlimpId' AND r.role_type IN ('W', 'ST')
            ORDER BY OrderBy, surname
          ");
        //sprawdz czy w bazie gate1 maja obecnosc i dodaj ja do obiektu jesli tak, jesli nie dodaj null i utworz nowa instancje
        $initialFormData = [];
        foreach ($members as &$member) {
            $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)
                ->findOneBy(['roleValidityId' => $member['rvid'], 'Meeting' => $meeting]);
            if (!$attendance) {
                $member['present'] = null;
            } else {
                $member['present'] = $attendance->getPresent();
            }
            $initialFormData['present'.$member['rvid']] = $member['present'];
        }

        //create a form with a submit button and a checkbox named 'presentRVID' for each member
        $formBuilder = $this->createFormBuilder($initialFormData)
            ->add('submit', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ));
        foreach ($members as &$member) {
            $formBuilder->add('present'.$member['rvid'], CheckboxType::class, array(
                'required' => false,
                'label' => false,
                'attr' => array('class' => 'form-control')
            ));
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            //retrieve or create a MeetingAttendance Object for each member and update it with the checkbox value
            foreach ($members as &$member) {
                $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)
                    ->findOneBy(['roleValidityId' => $member['rvid'], 'Meeting' => $meeting]);
                if (!$attendance) {
                    $attendance = new MeetingAttendance();
                    $attendance->setRoleValidityId($member['rvid']);
                    $attendance->setMeeting($meeting);
                }
                $attendance->setPresent($data['present'.$member['rvid']]);
                //save to DB
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($attendance);
                $entityManager->flush();
            }
        }

        return $this->render('knMeetings/attendance.html.twig', array(
            'kn' => $kn,
            'members' => $members,
            'meeting' => $meeting,
            'form' => $form->createView(),
        ));
    }
}