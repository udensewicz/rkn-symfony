<?php

namespace App\Controller;

use App\Entity\QAItem;
use App\Entity\QACategory;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\QACategoryType;

class QAController extends AbstractController
{

    /**
     * @Route("/qa", name="qa_list", methods="GET")
     */
    public function showAll() {
        $categories = $this->getDoctrine()->getRepository(QACategory::class)
            ->findBy(
                array('deleted' => false),
                array('ordering' => 'ASC')
            );

        return $this->render('qa/list.html.twig', array('categories' => $categories,));
    }

    /**
     * @Route("/qa/items", name="qa_items", methods="GET")
     */
    public function showItems() {
        $items = $this->getDoctrine()->getRepository(QAItem::class)
            ->findBy(
                array('deleted' => false),
                array('modifiedTime' => 'ASC')
            );

        return $this->render('qa/items_edit_list.html.twig', array('items' => $items,));
    }
    /**
     * @Route("/qa/categories", name="qa_categories")
     * Method({"GET", "POST"})
     */
    public function showCategories(Request $request) {
        $categories = $this->getDoctrine()->getRepository(QACategory::class)
            ->findBy(
                array('deleted' => false),
                array('ordering' => 'ASC')
            );
        $form = $this->createFormBuilder(['categories' => $categories])
            ->add('categories', CollectionType::class, [
                'entry_type' => QACategoryType::class,
                'label' => false,
                'entry_options' => ['label' => false],
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('qa_categories');
        }

        return $this->render('qa/categories_edit_list.html.twig', array(
            'categories' => $categories,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/qa/categories/new", name="new_qa_category")
     * Method({"GET", "POST"})
     */
    public function newCategory(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $cat = new QACategory();
            $cat->setDeleted(false);
            $form = $this->createFormBuilder($cat)
                ->add('name', TextType::class, array('label' => 'Nazwa', 'attr' => array('class' => 'form-control')))
                ->add('ordering', IntegerType::class, array(
                    'label' => 'Nr w kolejności',
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
                $item = $form->getData();
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($item);
                $entityManager->flush();
                return $this->redirectToRoute('qa_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowa kategoria pytań',
            ));
        }
    }

    /**
     * @Route("/qa/items/new", name="new_qa_item")
     * Method({"GET", "POST"})
     */
    public function newItem(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new QAItem();
            $item->setDeleted(false);
            $user = $this->getUser();
            $form = $this->createFormBuilder($item)
                ->add('category', EntityType::class, array(
                    'label' => 'Kategoria',
                    'class' => QACategory::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->andWhere('u.deleted = false')
                            ->orderBy('u.ordering', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('question', TextType::class, array('label' => 'Pytanie', 'attr' => array('class' => 'form-control')))
                ->add('ordering', IntegerType::class, array(
                    'label' => 'Nr w kolejności',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('answer', TextareaType::class, array(
                    'label' => 'Treść odpowiedzi',
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
                $item = $form->getData();
                $item->setCreatedBy($user);
                $item->setModifiedBy($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($item);
                $entityManager->flush();
                return $this->redirectToRoute('qa_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Nowe pytanie',
            ));
        }
    }

    /**
     * @Route("/qa/items/{id}/edit", name="edit_qa_item")
     * Method({"GET", "POST"})
     */
    public function editItem(Request $request, $id){
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = $this->getDoctrine()->getRepository(QAItem::class)->find($id);
            $user = $this->getUser();
            $form = $this->createFormBuilder($item)
                ->add('category', EntityType::class, array(
                    'label' => 'Kategoria',
                    'class' => QACategory::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->andWhere('u.deleted = false')
                            ->orderBy('u.ordering', 'ASC');
                    },
                    'choice_label' => 'name',
                    'attr' => array('class' => 'form-control')
                ))
                ->add('question', TextType::class, array('label' => 'Pytanie', 'attr' => array('class' => 'form-control')))
                ->add('ordering', IntegerType::class, array(
                    'label' => 'Nr w kolejności',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('answer', TextareaType::class, array(
                    'label' => 'Treść odpowiedzi',
                    'required' => false,
                    'attr' => array('textarea', 'class' => 'form-control')
                ))
                ->add('deleted', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Skasować pytanie?',
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
                $item->setModifiedBy($user);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->flush();
                return $this->redirectToRoute('qa_list');
            }
            return $this->render('addeditform.html.twig', array(
                'form' => $form->createView(),
                'title' => 'Edytuj pytanie',
            ));
        }
    }


    /**
     * @Route("/qa/{id}", name="qa_show", methods="GET")
     */
    public function show($id) {
        $question= $this->getDoctrine()->getRepository(QAItem::class)
            ->findOneBy(
                array(
                    'id' =>$id,
                    'deleted'=>false
                ));

        return $this->render('qa/show.html.twig', array('question' => $question));
    }

}
