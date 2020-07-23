<?php

namespace App\Form;

use App\Entity\MenuCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MenuCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => false, 'attr' => array('class' => 'form-control')))
            ->add('link', TextType::class, array('label' => false, 'required' => false,'attr' => array('class' => 'form-control')))
            ->add('ordering', IntegerType::class, array('label' => false, 'attr' => array('class' => 'form-control')))
            ->add('hasChildren', CheckboxType::class, array('label' => false, 'required' => false))
            ->add('deleted', CheckboxType::class, array('label' => false, 'required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuCategory::class,
        ]);
    }
}