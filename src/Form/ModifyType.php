<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ModifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', TextType::class, [
                'attr'  => [
                    'class'  => 'form-control'
                ]
            ])
            ->add('nom', TextType::class, [
                'attr'  => [
                    'class'  => 'form-control'
                ]
            ])
            ->add('numero', TextType::class, [
                'attr'  => [
                    'class'  => 'form-control'
                ]
            ])
            ->add('reference', TextType::class, [
                'attr'  => [
                    'class'  => 'form-control'
                ]
            ])
            ->add('IdReference', TextType::class, [
                'attr'  => [
                    'class'  => 'form-control'
                ]
            ])
            ->add('reseau', ChoiceType::class,[
                'attr' => [
                    'class' => "form-control"
                ],
                'choices'  => [
                    'MTN'  => 'MTN',
                    'MOOV'  => 'MOOV'   
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
