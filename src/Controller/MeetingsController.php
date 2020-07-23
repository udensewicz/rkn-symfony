<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingAttendance;
use App\Entity\User;
use App\Entity\Voting;
use App\Entity\PnapnVoting;

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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MeetingsController extends AbstractController
{
    /**
     * @Route("/meetings/all", name="meetings")
     * Method({"GET",})
     */
    public function meetings_list()
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin')) {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('published' => true, 'deleted' => false, 'researchGroup' => null),
                    array('dateStart' => 'DESC')
                );
        }
        else {
            $meetings = $this->getDoctrine()->getRepository(Meeting::class)
                ->findBy(
                    array('deleted' => false, 'researchGroup' => null),
                    array('dateStart' => 'DESC')
                );
        }
        return $this->render('meetings/list.html.twig', array(
            'meetings' => $meetings
        ));
    }


    /**
    //     * @Route("/meetings/new", name="new_meeting")
    //     * Method({"GET", "POST"})
    //     */
    public function new_meeting(Request $request, Connection $connection){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new Meeting();
            $del = FALSE;
            $item->setDeleted($del);
            $user = $this->getUser();
            $form = $this->createFormBuilder($item)
                ->add('title', TextType::class, array(
                    'label' => 'Nazwa posiedzenia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('published', CheckboxType::class, array(
                    'label' => 'Opublikowane?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
                ))
                ->add('plan', TextareaType::class, array(
                    'label' => 'Plan posiedzenia',
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
                ->add('have_pnapn', CheckboxType::class, array(
                    'label' => 'Posiedzenie PnaPN?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
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
                if ($item->getHavePnapn()){
                    //add a mirror meeting to Olimp for use in eW
                    $title = $item->getTitle();;
                    $createdBy = $user->getOlimpId();
                    $stmt = $connection->prepare("INSERT INTO rkn.meeting
                        VALUES (default, '$title', TRUE, ' ', ?, 1, ?, '$createdBy', TRUE, FALSE)");
                    $stmt->bindValue(1, $item->getDateStart(), "datetime");
                    $stmt->bindValue(2, $item->getDateEnd(), "datetime");
                    $stmt->execute();
                    $meetingOlimpId = $connection->lastInsertId();
                    $item->setOlimpId($meetingOlimpId);
                    //create an empty pnapn_list
                    $connection->insert('rkn.pnapn_list', array('id' => $meetingOlimpId));
                    //create a voting for project
                    $pnapn = new PnapnVoting();
                    $pnapn->setMeeting($item);
                    //marker voting is a Voting object whose related votes have status '55' if vote is saved
                    //(can be edited) and '99' if vote is in urn. they keep track of whether user can change their vote
                    $pnapnMarkerVoting = new Voting();
                    $pnapnMarkerVoting->setSubject($item->getTitle());
                    $pnapnMarkerVoting->setMeeting($item);
                    $entityManager->persist($pnapn);
                    $entityManager->persist($pnapnMarkerVoting);
                };
                $entityManager->persist($item);
                $entityManager->flush();

                return $this->redirectToRoute('meetings');
            }

            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowe posiedzenie',
                'textareaheight' => 200,
            ));
        }
    }

    /**
     * @Route("/meetings/{id}/edit", name="edit_meeting")
     * Method({"GET", "POST"})
     */
    public function edit_meeting(Request $request, $id, Connection $connection)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);
            $form = $this->createFormBuilder($meeting)
                ->add('title', TextType::class, array(
                    'label' => 'Nazwa posiedzenia',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('published', CheckboxType::class, array(
                    'label' => 'Opublikowane?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
                ))
                ->add('plan', TextareaType::class, array(
                    'label' => 'Plan posiedzenia',
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
                ->add('have_pnapn', CheckboxType::class, array(
                    'label' => 'Posiedzenie PnaPN?',
                    'required' => false,
                    'attr' => array('class' => 'form-check')
                ))
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować posiedzenie?',
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
                if ($meeting->getHavePnapn()){
                    $item = $form->getData();
                    if ($meeting->getPnapnVoting()) {
                        //change mirror meeting to Olimp for use in eW
                        $title = $item->getTitle();
                        $olimpId = $item->getOlimpId();
                        if ($olimpId) {
                            $stmt = $connection->prepare("UPDATE rkn.meeting 
                            SET title = '$title', date_start = ?, date_end = ?
                            WHERE id = '$olimpId'");
                            $stmt->bindValue(1, $item->getDateStart(), "datetime");
                            $stmt->bindValue(2, $item->getDateEnd(), "datetime");
                            $stmt->execute();
                        }
                    }
                    else {
                        //add a mirror meeting to Olimp for use in eW and a pnapn_list item
                        $title = $item->getTitle();;
                        $createdBy = $this->getUser()->getOlimpId();
                        $stmt = $connection->prepare("INSERT INTO rkn.meeting
                        VALUES (default, '$title', TRUE, ' ', ?, 1, ?, '$createdBy', TRUE, FALSE)");
                        $stmt->bindValue(1, $item->getDateStart(), "datetime");
                        $stmt->bindValue(2, $item->getDateEnd(), "datetime");
                        $stmt->execute();
                        $meetingOlimpId = $connection->lastInsertId();
                        $item->setOlimpId($meetingOlimpId);
                        //create an empty pnapn_list
                        $connection->insert('rkn.pnapn_list', array('id' => $meetingOlimpId));

                        //create a voting for project
                        $pnapn = new PnapnVoting();
                        $pnapn->setMeeting($item);
                        //marker voting is a Voting object whose related votes have status '55' if vote is saved
                        //(can be edited) and '99' if vote is in urn. they keep track of whether user can change their vote
                        $pnapnMarkerVoting = new Voting();
                        $pnapnMarkerVoting->setSubject($item->getTitle());
                        $pnapnMarkerVoting->setMeeting($item);
                        $entityManager->persist($pnapn);
                        $entityManager->persist($pnapnMarkerVoting);
                    }
                }
                $entityManager->flush();
                return $this->redirectToRoute('meetings');
            }

            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj posiedzenie',
                'textareaheight' => 200,
            ));
        }
    }

    /**
     * @Route("/meetings/{id}/attendance", name="edit_attendance")
     * Method({"GET", "POST"})
     */
    public function attendance_view (Request $request, $id, Connection $connection) {
        $meeting = $this->getDoctrine()->getRepository(Meeting::class)->find($id);
        $dateStart = $meeting->getDateStart()->format('Y-m-d H:i:s');
        $dateEnd = $meeting->getDateEnd()->format('Y-m-d H:i:s');
        //wycianij z olimpa wszystkich aktywnych delegatow na czas posiedzenia
        $delegaci = $connection->fetchAll("SELECT c.uid, c.rvid, u.name, u.surname, o.nazwa as kn
            FROM mainframe.f_roles_active_for_date('$dateStart', '$dateEnd') c
            LEFT JOIN mainframe.login u ON u.id = c.uid
            LEFT JOIN mainframe.v_unist_short o ON o.id = c.org_id2
            WHERE c.org_id = 54223 AND role_type = 'W'
            ORDER BY surname
          ");
        //sprawdz czy w bazie gate1 maja obecnosc i dodaj ja do obiektu jesli tak, jesli nie dodaj null i utworz nowa instancje
        $initialFormData = [];
        foreach ($delegaci as &$delegat) {
            $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)
                ->findOneBy(['roleValidityId' => $delegat['rvid'], 'Meeting' => $meeting]);
            if (!$attendance) {
                $delegat['present'] = null;
            } else {
                $delegat['present'] = $attendance->getPresent();
            }
            $initialFormData['present'.$delegat['rvid']] = $delegat['present'];
        }

        //create a form with a submit button and a checkbox named 'presentRVID' for each delegate
        $formBuilder = $this->createFormBuilder($initialFormData)
                ->add('submit', SubmitType::class, array(
                    'label' => 'Zapisz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ));
        foreach ($delegaci as &$delegat) {
            $formBuilder->add('present'.$delegat['rvid'], CheckboxType::class, array(
                'required' => false,
                'label' => false,
                'attr' => array('class' => 'form-control')
            ));
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            //retrieve or create a MeetingAttendance Object for each delegate and update it with the checkbox value
            foreach ($delegaci as &$delegat) {
                $attendance = $this->getDoctrine()->getRepository(MeetingAttendance::class)
                    ->findOneBy(['roleValidityId' => $delegat['rvid'], 'Meeting' => $meeting]);
                if (!$attendance) {
                    $attendance = new MeetingAttendance();
                    $attendance->setRoleValidityId($delegat['rvid']);
                    $attendance->setMeeting($meeting);
                }
                $attendance->setPresent($data['present'.$delegat['rvid']]);
                //save to DB
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($attendance);
                $entityManager->flush();
            }
        }

        return $this->render('meetings/attendance.html.twig', array(
            'delegaci' => $delegaci,
            'meeting' => $meeting,
            'form' => $form->createView(),
        ));
    }
}