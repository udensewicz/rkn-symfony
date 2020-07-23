<?php

namespace App\Controller;

use App\Entity\File;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Form\FormValidationType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class FilesController extends AbstractController
{

    /**
     * @Route("/files/new", name="new_file")
     * Method({"GET", "POST"})
     */
    public function newFile(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $file = new File();
            $del = FALSE;
            $user = $this->getUser();
            $file->setDeleted($del);
            $file->setCreated(new \DateTime());
            $form = $this->createFormBuilder($file)
                ->add('name', TextType::class, array('label' => 'Nazwa', 'attr' => array('class' => 'form-control')))
                ->add('accessType', ChoiceType::class, array(
                    'attr' => array('class' => 'custom-select'),
                    'label' => 'Dostęp do pliku',
                    'choices' => array(
                        'Wszyscy użytkownicy' => 'ALL_USERS',
                        'Tylko zalogowani użytkownicy (dane wrażliwe)' => 'LOGGED_IN_ONLY',
                    ),
                ))
                ->add('file', FileType::class, array('label' => 'Wstaw plik (pdf, doc, xml, ppt)', 'attr' => array('class' => 'form-control-file')))
                ->add('save', SubmitType::class, array(
                    'label' => 'Utwórz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ))
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $uploaded = $form->get('file')->getData();
                $fileName = 'file' . $this->generateUniqueFileName();
                $extension = $uploaded->guessExtension();
                $uploaded->move($this->getParameter('files_directory'), $fileName.'.'.$extension);
                $file->setFile($fileName);
                $file->setExtension($extension);
                $file->setCreator($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($file);
                $entityManager->flush();
                return $this->redirectToRoute('file_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowy plik'
            ));
        }
    }

    /**
     * @Route("/files/{id}/edit", name="edit_files")
     * Method({"GET", "POST"})
     */
    public function editFile(Request $request, $id) {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $file= $this->getDoctrine()->getRepository(File::class)->find($id);
            $form = $this->createFormBuilder($file)
                ->add('name', TextType::class, array('label' => 'Nazwa', 'attr' => array('class' => 'form-control')))
                ->add('accessType', ChoiceType::class, array(
                    'attr' => array('class' => 'custom-select'),
                    'label' => 'Dostęp do pliku',
                    'choices' => array(
                        'Wszyscy użytkownicy' => 'ALL_USERS',
                        'Tylko zalogowani użytkownicy (dane wrażliwe)' => 'LOGGED_IN_ONLY',
                    ),
                ))
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować?',
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
                return $this->redirectToRoute('file_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj plik'
            ));
        }
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }


    /**
     * @Route("/files/list", name="file_list", methods="GET")
     */
    public function listFiles() {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $file = $this->getDoctrine()->getRepository(File::class)
                ->findBy(
                    array('deleted' => 'false'),
                    array('created' => 'DESC')
                );

            return $this->render('files/list.html.twig', array('file' => $file));
        }
    }

    /**
     * @Route("/files/{name}/", name="file_show", methods="GET")
     */
    public function showFile($name) {
        $file= $this->getDoctrine()->getRepository(File::class)->findOneBy(['file' => $name]);
        $extension = $file->getExtension();
        $location = '/www/rkn-symfony/public/uploads/files/'.$name.'.'.$extension;
        $mimetype = mime_content_type($location);

        if ($file->getAccessType() != 'ALL_USERS' and !$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->render('files/notallowed.html.twig');
        }
        else if (file_exists($location)) {
//             download  header('Content-Disposition: attachment; filename='.$file['name_orginal']);
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mimetype);
            header('Content-Disposition: inline; filename=' .$name.'.'.$extension);
            header('Expires: 0');
            header('Cache-Control: private', false);
            header('Pragma: public');
            header('Content-Length: ' . filesize($location));
            header('Pragma: no-cache');

            readfile($location);
        }
        return 0;
    }
}
