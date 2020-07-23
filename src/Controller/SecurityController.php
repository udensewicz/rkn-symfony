<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface; //delete later

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }


    //a function to call to copy a user of selected ID to gate1 DB

//    /**
//     * @Route("/user/{id}", name="user_view")
//     */
//    public function user($id, Connection $connection): Response
//    {
//        $data = $connection->fetchAssoc("SELECT password, salt, id, name, surname, login
//    					FROM mainframe.login
//  						WHERE id = '$id'");
//        $res = $connection->fetchAll("SELECT role_type
//                        FROM mainframe.vroles_active_extend
//                        WHERE org_id = 54223 AND uid='$id'");
//        $roles = array();
//        if ($res != NULL) {
//            foreach ($res as &$value) {
//                array_push($roles, strval('ROLE_'.$value['role_type']));
//            }
//        }
//        $user = new User();
//        $user->setEmail($data['login']);
//        $user->setName($data['name']);
//        $user->setSurname($data['surname']);
//        $user->setOlimpID($data['id']);
//        $user->setPassword($data['password']);
//        $user->setSalt($data['salt']);
//        $user->setRoles($roles);
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityManager->persist($user);
//        $entityManager->flush();
//
//        return $this->render('security/user.html.twig', ['member' => $data]);
//    }
}
