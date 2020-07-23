<?php

namespace App\Form;

use App\Entity\PnapnVote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PnapnVoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vote_cat1', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Wartość naukowo-dydaktyczna (0.35)',
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5]
            ))
            ->add('vote_cat2', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Innowacyjność (0.2)',
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5]
            ))
            ->add('vote_cat3', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Potencjał rozwojowy (0.2)',
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5]
            ))
            ->add('vote_cat4', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Liczba beneficjentów (0.15)',
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5]
            ))
            ->add('vote_cat5', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => 'Medialność (0.1)',
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5]
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PnapnVote::class,
        ]);
    }
}