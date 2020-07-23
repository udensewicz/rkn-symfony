<?php

namespace App\Form;

use App\Entity\PnapnProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Form\PnapnProjectType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProjectListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projects', CollectionType::class, [
                'entry_type' => PnapnProjectType::class,
                'label' => false,
                'entry_options' => ['label' => false],
            ])
            ->add('submit', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary mt-3'),
                'label' => 'Zapisz status projektów',
            ))
            ->getForm();
    }

//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults([
//            'data_class' => PnapnProject::class,
//        ]);
//    }
}