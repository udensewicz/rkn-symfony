<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ArticleController extends AbstractController
{


    /**
     * @Route("/", name="index", methods="GET")
     */
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)
            ->findBy(
                array('articleType'=> 'index'),
                array('modifiedTime' => 'DESC')
            );


        return $this->render('index.html.twig', array('articles' => $articles));
    }

    /**
     * @Route("/article/new", name="new_article")
     * Method({"GET", "POST"})
     */
    public function new_article(Request $request) {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $article = new Article();
            $del = FALSE;
            $article->setDeleted($del);
            $user = $this->getUser();
            $form = $this->createFormBuilder($article)
                ->add('title', TextType::class, array('label' => 'Tytuł', 'attr' => array('class' => 'form-control')))
                ->add('articleType', ChoiceType::class, array(
                    'attr' => array('class' => 'custom-select'),
                    'label' => 'Typ Artykułu',
                    'choices' => array(
                        'Artykuł na stronę główną' => 'index',
                        'Archiwizuj (z głównej strony)' => 'archiwum',
                        'test' => 'test',
                        'Artykuł z informacjami' => 'info',
                    ),
                ))
                ->add('body', TextareaType::class, array(
                    'label' => 'Treść Artykułu',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Utwórz',
                    'attr' => array('class' => 'btn btn-primary mt-3')
                ))
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $article = $form->getData();
                $article->setCreatedBy($user);
                $article->setModifiedBy($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($article);
                $entityManager->flush();
                return $this->redirectToRoute('article_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowy artykuł',
            ));
        }
    }

    /**
     * @Route("/article/{id}/edit", name="edit_article")
     * Method({"GET", "POST"})
     */
    public function edit_article(Request $request, $id) {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
            $user = $this->getUser();
            $form = $this->createFormBuilder($article)
                ->add('title', TextType::class, array('label' => 'Tytuł', 'attr' => array('class' => 'form-control')))
                ->add('articleType', ChoiceType::class, array(
                    'attr' => array('class' => 'custom-select'),
                    'label' => 'Typ Artykułu',
                    'choices' => array(
                        'Artykuł na stronę główną' => 'index',
                        'Archiwizuj (z głównej strony)' => 'archiwum',
                        'test' => 'test',
                        'Artykuł z informacjami' => 'info',
                    ),
                ))
                ->add('body', TextareaType::class, array(
                    'label' => 'Treść Artykułu',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować artykuł?',
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
                $article->setModifiedBy($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('article_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj artykuł',
            ));
        }
    }

    /**
     * @Route("/article/list", name="article_list", methods="GET")
     */
    public function articles() {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $articles = $this->getDoctrine()->getRepository(Article::class)
                ->findBy(
                    array('deleted' => false),
                    array('modifiedTime' => 'DESC')
                );

            return $this->render('article/list.html.twig', array('articles' => $articles,));
        }
    }

    /**
     * @Route("/article/{id}", name="article_show", methods="GET")
     */
    public function show($id) {
        $article= $this->getDoctrine()->getRepository(Article::class)
            ->findOneBy(
                array(
                    'id' =>$id,
                    'deleted'=>false
                ));

        return $this->render('article/show.html.twig', array('article' => $article));
    }


    /**
     * @Route("/news/{slug}")
     */
    public function showd($slug)
    {
        $comments = [
            'DDDDDDDDDDD',
            'XD',
            'XDDDDDDDDDDDDDDDDD',
        ];
        return $this->render('testshow.html.twig', [
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'comments' => $comments,
        ]);
    }
}
