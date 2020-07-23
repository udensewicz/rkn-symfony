<?php

namespace App\Form;

use App\Entity\VotingGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class VotingGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $votingGroup = $builder->getData();
        foreach ($votingGroup->getVotings() as &$voting){
            $builder->add('voting'.$voting->getId(), ChoiceType::class, array(
                'mapped' => false,
                'attr' => array('class' => 'form-control'),
                'label' => $voting->getSubject(),
                'choices' => array(
                    'Za' => '1',
                    'Przeciw' => '2',
                    'Wstrzymuję się' => '3',
                ),
            ));
        }
        $builder->add('submit', SubmitType::class, array(
            'attr' => array('class' => 'btn btn-primary mt-3'),
            'label' => 'Zagłosuj',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VotingGroup::class,
        ]);
    }
}