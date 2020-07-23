<?php

namespace App\Form;

use App\Entity\PnapnProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PnapnProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('presentationOrder', IntegerType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => false,
                'required' => false,
            ))
            ->add('status', ChoiceType::class, array(
                'attr' => array('class' => 'form-control'),
                'label' => false,
                'choices' => [
                    "Dostarczono prezentację" => "RKN_PRESENTATION_OK",
                    "Oczekuje" => "WAITING_RKN",
                    "Zgłoszenie odrzucono" => "RKN_SAD_REJECT",
                    "Koło wycofało się" => "RKN_CHICKEN_OUT",
                    'Projekt otrzymał środki' => 'WAITING_RKN_GET_CASH',
                    'Projekt nie otrzymał środków' => 'WAITING_RKN_DONT_GET_CASH'
                ]
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PnapnProject::class,
        ]);
    }
}