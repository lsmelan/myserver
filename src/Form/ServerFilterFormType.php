<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServerFilterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ram', ChoiceType::class, [
                'choices' => [
                    '2GB' => '2GB',
                    '4GB' => '4GB',
                    '8GB' => '8GB',
                    '12GB' => '12GB',
                    '16GB' => '16GB',
                    '24GB' => '24GB',
                    '32GB' => '32GB',
                    '48GB' => '48GB',
                    '64GB' => '64GB',
                    '96GB' => '96GB'
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('hdd', ChoiceType::class, [
                'placeholder' => 'Hard-disk Type',
                'choices' => [
                    'SAS' => 'SAS',
                    'SATA' => 'SATA',
                    'SSD' => 'SSD',
                ],
            ])
            ->add('location', ChoiceType::class, [
                'placeholder' => 'Location',
                'choices' => [
                    'AmsterdamAMS-01' => 'AmsterdamAMS-01',
                    'DallasDAL-10' => 'DallasDAL-10',
                    'FrankfurtFRA-10' => 'FrankfurtFRA-10',
                    'Hong KongHKG-10' => 'Hong KongHKG-10',
                    'San FranciscoSFO-12' => 'San FranciscoSFO-12',
                    'SingaporeSIN-11' => 'SingaporeSIN-11',
                    'Washington D.C.WDC-01' => 'Washington D.C.WDC-01',
                ],
            ])
            ->add('page', HiddenType::class, [
                'data' => '1',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
