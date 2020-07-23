<?php

namespace App\Form;

use App\Entity\MenuItem;
use App\Entity\MenuCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => false, 'attr' => array('class' => 'form-control')))
            ->add('link', TextType::class, array('label' => false, 'required' => false,'attr' => array('class' => 'form-control')))
            ->add('ordering', IntegerType::class, array('label' => false, 'attr' => array('class' => 'form-control')))
            ->add('parent', EntityType::class, array(
                'label' => false,
                'class' => MenuCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.deleted = false')
                        ->andWhere('u.hasChildren = true');
                },
                'choice_label' => 'name',
                'attr' => array('class' => 'form-control')
            ))
            ->add('deleted', CheckboxType::class, array('label' => false, 'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
        ]);
    }
}