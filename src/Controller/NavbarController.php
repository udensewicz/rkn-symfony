<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Entity\MenuCategory;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\FormValidationType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\MenuCategoryType;
use App\Form\MenuItemType;

class NavbarController extends AbstractController
{
    public function navbar()
    {
        $categories = $this->getDoctrine()->getRepository(MenuCategory::class)
            ->findBy(
                array('deleted' => false),
                array('ordering' => 'ASC')
            );
        $items = $this->getDoctrine()->getRepository(MenuItem::class)
            ->findBy(
                array('deleted' => false),
                array('ordering' => 'ASC')
            );

        return $this->render('inc/navbar.html.twig', array('categories' => $categories, 'items' => $items));
    }


    /**
     * //     * @Route("/menu", name="edit_navbar")
     * //     * Method({"GET", "POST"})
     * //     */
    public function edit_navbar(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $items = $this->getDoctrine()->getRepository(MenuCategory::class)
                ->findBy(
                    array('deleted' => false),
                    array('ordering' => 'ASC')
                );

            $form = $this->createFormBuilder(['items' => $items])
                ->add('items', CollectionType::class, [
                    'entry_type' => MenuCategoryType::class,
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
                return $this->redirectToRoute('edit_navbar');
            }

            return $this->render('menu/edit_navbar.html.twig', array(
                'items' => $items,
                'form' => $form->createView()
            ));
        }
    }

    /**
     * //     * @Route("/menu/add", name="add_category")
     * //     * Method({"GET", "POST"})
     * //     */
    public function add_category(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new MenuCategory();
            $del = FALSE;
            $item->setDeleted($del);
            $form = $this->createFormBuilder($item)
                ->add('name', TextType::class, array('label' => 'Nazwa kategorii', 'attr' => array('class' => 'form-control')))
                ->add('link', TextType::class, array('label' => 'Link (opcjonalnie)', 'attr' => array('class' => 'form-control')))
                ->add('ordering', TextType::class, array('label' => 'Nr w kolejności', 'attr' => array('class' => 'form-control')))
                ->add('hasChildren', ChoiceType::class, array(
                    'expanded' => true,
                    'attr' => array('class' => 'form-check'),
                    'label' => 'Czy ma elementy podrzędne',
                    'choices' => array(
                        'Nie' => false,
                        'Tak' => true,
                    ),
                    'data' => false,
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
                return $this->redirectToRoute('edit_navbar');
            }

            return $this->render('menu/add.html.twig', array(
                'form' => $form->createView(),
                'category' => FALSE
            ));
        }
    }

    /**
     * //     * @Route("/menu/{id}/items", name="edit_items")
     * //     * Method({"GET", "POST"})
     * //     */
    public function edit_items(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $category = $this->getDoctrine()->getRepository(MenuCategory::class)->find($id);
            $items = $this->getDoctrine()->getRepository(MenuItem::class)
                ->findBy(
                    array('deleted' => false, 'Parent' => $category),
                    array('ordering' => 'ASC')
                );

            $form = $this->createFormBuilder(['items' => $items])
                ->add('items', CollectionType::class, [
                    'entry_type' => MenuItemType::class,
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
                return $this->redirectToRoute('edit_items', ['id' => $id]);
            }

            return $this->render('menu/edit_items.html.twig', array(
                'items' => $items,
                'category' => $category,
                'form' => $form->createView()
            ));
        }
    }

    /**
     * //     * @Route("/menu/{id}/add", name="add_item")
     * //     * Method({"GET", "POST"})
     * //     */
    public function add_item(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new MenuItem();
            $del = FALSE;
            $item->setDeleted($del);
            $parent = $this->getDoctrine()->getRepository(MenuCategory::class)->find($id);
            $item->setParent($parent);
            $form = $this->createFormBuilder($item)
                ->add('name', TextType::class, array('label' => 'Nazwa pola', 'attr' => array('class' => 'form-control')))
                ->add('link', TextType::class, array('label' => 'Link', 'attr' => array('class' => 'form-control')))
                ->add('ordering', TextType::class, array('label' => 'Nr w kolejności', 'attr' => array('class' => 'form-control')))
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
                return $this->redirectToRoute('edit_items', array('id' => $id));
            }

            return $this->render('menu/add.html.twig', array(
                'form' => $form->createView(),
                'category' => $parent->getName()
            ));
        }
    }
    /**
     * //     * @Route("/menu/{id}/add_divider", name="add_divider")
     * //     * Method({"GET", "POST"})
     * //     */
    public function add_divider(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_site_admin'))
            return $this->render('notallowed.html.twig');
        else {
            $item = new MenuItem();
            $del = FALSE;
            $item->setDeleted($del);
            $parent = $this->getDoctrine()->getRepository(MenuCategory::class)->find($id);
            $item->setParent($parent);
            $item->setName('<divider>');
            $item->setLink('!#');
            $form = $this->createFormBuilder($item)
                ->add('ordering', IntegerType::class, array('label' => 'Nr w kolejności', 'attr' => array('class' => 'form-control')))
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
                return $this->redirectToRoute('edit_items', array('id' => $id));
            }

            return $this->render('menu/add.html.twig', array(
                'form' => $form->createView(),
                'category' => $parent->getName()
            ));
        }
    }
}