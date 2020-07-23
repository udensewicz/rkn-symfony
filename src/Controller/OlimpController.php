<?php

namespace App\Controller;

use App\Entity\ResearchGroup;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OlimpController extends AbstractController
{
    /**
     * @Route("/listaKN", name="listaKN")
     */
    public function index(Connection $connection)
    {


        $resw = $connection->fetchAll("SELECT o.id, o.skrot, o.nazwa, o.org_type AS typ, r.skrot AS rel
													FROM mainframe.v_unist_short o
													LEFT JOIN mainframe.v_unist_short r ON r.id = o.rel_org
						WHERE o.org_type = 1318151 AND o.active = true ORDER BY r.nazwa, o.nazwa 
          ");

        $res = $connection->fetchAll("SELECT o.id, o.skrot, o.nazwa, o.org_type AS typ, r.skrot AS rel, w.nazwa AS wydzial
													FROM mainframe.v_unist_short o
													LEFT JOIN mainframe.v_unist_short r ON r.id = o.rel_org
													LEFT JOIN mainframe.v_unist_short w ON w.id = o.wydzial_id
						WHERE o.org_type = 25152 AND o.active = true ORDER BY r.nazwa, o.nazwa 
          ");

        $reswww = $connection->fetchAll("SELECT e.contact AS www, e.org_id AS id, e.type AS type, r.opis AS opis
                                    FROM ew.contacts e
                                    LEFT JOIN ew.organizacje r ON r.id = e.org_id
                                        WHERE type = 163842 OR type =163861 
                                        ");

        return $this->render('olimp/index.html.twig', array('orgs' => $res, 'wydzials' => $resw, 'wwws' => $reswww,));
    }

    public function carousel(Connection $connection)
    {
        $logos = $connection->fetchAll(" SELECT z.id, z.contact, z.nazwa FROM 
            (SELECT o.id, o.nazwa, e.type, e.contact, CASE WHEN EXISTS (SELECT contact FROM ew.contacts WHERE type=163861)
                  THEN 163861
                  ELSE 163842
               END AS default_type
             FROM mainframe.unit_logo l 
             LEFT JOIN mainframe.v_unist_short o ON o.id = l.org_id 
             LEFT JOIN ew.contacts e ON o.id = e.org_id
             WHERE o.org_type=25152 AND l.deleted='f') z
         WHERE z.type=z.default_type
        ");
        $slides = [];
        $currentSlide = [];
        # group the logos into fours
        foreach ($logos as &$logo){
            array_push($currentSlide, $logo);
            if (count($currentSlide) == 4){
                array_push($slides, $currentSlide);
                $currentSlide = [];
            }
        }
        # fill the last group with logos from the front if necessary
        if (count($currentSlide) > 0) {
            $missingLogos = 4 - count($currentSlide);
            array_push($currentSlide, ...array_slice($logos, 0, $missingLogos));
            array_push($slides, $currentSlide);
        }

        return $this->render('KN lists/KNcarousel.html.twig', ['logos' => $logos, 'slides' => $slides]);
    }

    /**
     * @Route("/team", name="team")
     */
    public function team(Connection $connection)
    {

        $res = $connection->fetchAll("SELECT o.uid AS id, o.org_id, o.role_type AS rola, o.name, o.surname, r.contact AS mail, r.public AS mailpub, r.type, l.sex as gender  
													FROM mainframe.vroles_active_extend o
													LEFT JOIN ew.contacts r ON r.uid = o.uid
													LEFT JOIN mainframe.login as l ON l.id = o.uid
						WHERE ((o.org_id = 54223 AND o.role_type = 'CHAIRMAN') 
						OR (o.org_id = 54223 AND o.role_type = 'D')
						OR (o.org_id = 54223 AND o.role_type = 'BM')  ) 
						ORDER BY r.uid, o.uid
          ");
        if ($res != NULL) {
            $chairman['mail'] = 'NULL';
            $chairman['phone'] = 'NULL';
            foreach ($res as &$value) {
                if ($value['rola'] == 'CHAIRMAN') {
                    $chairman['name'] = $value['name'];
                    $chairman['surname'] = $value['surname'];
                    $chairman['id'] = $value['id'];
                    $chairman['gender'] = $value['gender'];
                    $chairman['photo'] = 'https://mainframe.sspw.pl/?site=8010&uid=' . $chairman['id'] . '';
                    if ($value['type'] == 163851 and $value['mailpub'] == 200) {
                        $chairman['mail'] = $value['mail'];
                    };
                    if ($value['type'] == 163852 and $value['mailpub'] == 200){
                        $chairman['phone'] = $value['mail'];
                    };
                }
            }
//            dd($chairman);
            unset($value);
            $pnpn['mail'] = 'NULL';
            $pnpn['phone'] = 'NULL';
            foreach ($res as &$value) {
                if ($value['rola'] == 'D' AND $value['id'] != $chairman['id']) {
                    $pnpn['name'] = $value['name'];
                    $pnpn['surname'] = $value['surname'];
                    $pnpn['id'] = $value['id'];
                    $pnpn['gender'] = $value['gender'];
                    $pnpn['photo'] = 'https://mainframe.sspw.pl/?site=8010&uid=' . $pnpn['id'] . '';
                    if ($value['type'] == 163851 and $value['mailpub'] == '200') {
                        $pnpn['mail'] = $value['mail'];
                    };
                    if ($value['type'] == 163852 and $value['mailpub'] == 200){
                        $pnpn['phone'] = $value['mail'];
                    };
                }
            }
            unset($value);
            $ogolny['mail'] = 'NULL';
            $ogolny['phone'] = 'NULL';
            foreach ($res as &$value) {
                if ($value['rola'] == 'BM' AND $value['id'] != $pnpn['id'] and $value['id'] != $chairman['id']) {
                    $ogolny['name'] = $value['name'];
                    $ogolny['surname'] = $value['surname'];
                    $ogolny['id'] = $value['id'];
                    $ogolny['gender'] = $value['gender'];
                    $ogolny['photo'] = 'https://mainframe.sspw.pl/?site=8010&uid=' . $ogolny['id'] . '';
                    if ($value['type'] == 163851 and $value['mailpub'] == 200) {
                        $ogolny['mail'] = $value['mail'];
                    };
                    if ($value['type'] == 163852 and $value['mailpub'] == 200){
                        $ogolny['phone'] = $value['mail'];
                    };
                }
            }
            unset($value);
        }

        return $this->render('olimp/team.html.twig', array(
            'chairman' => $chairman,
            'pnpn' => $pnpn,
            'ogolny' => $ogolny
        ));
    }


    /**
     * @Route("/delegaci", name="delegaci")
     */
    public function delegaci(Connection $connection)
    {

        $delegaci = $connection->fetchAll("SELECT k.nazwa AS kn, o.name AS name, o.surname AS surname 
													FROM mainframe.vroles_active_extend o
													LEFT JOIN mainframe.v_unist_short k ON o.org_id2 = k.id
						WHERE (o.org_id = 54223 AND o.deleted = false AND o.role_type = 'W') 
						ORDER BY surname
          ");

        return $this->render('olimp/delegaci.html.twig', array(
            'delegaci' => $delegaci
        ));
    }

    /**
     * @Route("/kn/{id}/members", name="kn_members")
     */
    public function knMembers(Connection $connection, $id)
    {
        $kn = $picture = $this->getDoctrine()->getRepository(ResearchGroup::class)->find($id);
        $knId = $kn->getOlimpID();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_W_'.$knId) &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_ST_'.$knId))
            return $this->render('notallowed.html.twig');
        else {
            $members = $connection->fetchAll("SELECT name, surname, role_type, CASE role_type WHEN 'W' THEN 1 ELSE 2 END AS OrderBy
                FROM mainframe.vroles_active_extend 
                WHERE (org_id = '$knId' AND deleted = false AND role_type IN ('W', 'ST')) 
                ORDER BY OrderBy, surname
          ");

            return $this->render('olimp/knMembers.html.twig', array(
                'members' => $members,
                'kn' => $kn
            ));
        }
    }
}
