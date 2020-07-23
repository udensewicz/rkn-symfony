<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\ResearchGroup;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->connection = $connection;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) //, Connection $connection)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $connection = $this->connection;
        $entityManager = $this->entityManager;
        $email = $credentials['email'];
        //get user from olimp
        $data = $connection->fetchAssoc("SELECT password, salt, id, name, surname
    					FROM mainframe.login
  						WHERE login = '$email'");
        if (!$data) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Nie znaleziono takiego użytkownika.');
        }
        //get user roles in RKN from olimp
        $userID = $data['id'];
        $res = $connection->fetchAll("SELECT role_type
                        FROM mainframe.vroles_active_extend
                        WHERE org_id = 54223 AND uid='$userID'");
        $roles = [];
        if ($res != NULL) {
            foreach ($res as &$value) {
                array_push($roles, strval('ROLE_'.$value['role_type']));
            }
        }
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        //create user if not in database
        if (!$user) {
            $user = new User();
            $user->setEmail($credentials['email']);
        }
        //get user roles in KN from olimp
        $knRoles = $connection->fetchAll("SELECT role_type, org_id, o_skrot1, o_nazwa1, o_nazwa_d1
                        FROM mainframe.vroles_active_extend
                        WHERE org_type=25152 AND uid='$userID'");
        if ($knRoles != NULL) {
            foreach ($knRoles as &$knRole){
                //get or create a KN instance and update data
                $kn = $this->entityManager->getRepository(ResearchGroup::class)->findOneBy(['olimpId' => $knRole['org_id']]);
                if (!$kn){
                    $kn = new ResearchGroup();
                    $kn->setOlimpId($knRole['org_id']);
                }
                $kn->setName($knRole['o_nazwa1']);
                $kn->setNameD($knRole['o_nazwa_d1']);
                $kn->setNameShort($knRole['o_skrot1']);
                $entityManager->persist($kn);
                $entityManager->flush();
                $user->addResearchGroup($kn);
                array_push($roles, strval('ROLE_'.$knRole['role_type'].'_'.$knRole['org_id']));
            }
        }
        //update user data
        $user->setName($data['name']);
        $user->setSurname($data['surname']);
        $user->setOlimpID($data['id']);
        $user->setPassword($data['password']);
        $user->setSalt($data['salt']);
        $user->setRoles($roles);
        //loop through KNs user had before and remove them if they're not in olimp query result (user stopped being member)
        foreach ($user->getResearchGroups() as &$oldKn){
            $olimpIdsArray = array_map(function($item){return $item['org_id'];}, $knRoles);
            if (!in_array($oldKn->getOlimpId(), $olimpIdsArray)){
                $user->removeResearchGroup($oldKn);
            }
        };
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('index'));
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}
