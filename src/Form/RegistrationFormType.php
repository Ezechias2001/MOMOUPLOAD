<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('prenom', TextType::class, [
            'attr' => [
                'class' =>'form-control',
                'placeholder' =>"Jacob",
                'id' => 'signupFormFirstName'
            ], 
            'label' => false
        ])
        ->add('nom', TextType::class, [
            'attr' => [
                'class' =>'form-control',
                'placeholder' =>"Williams",
                'id' => 'signupFormLasttName'
            ], 
            'label' => false
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'class' =>'form-control',
                'placeholder' =>"email@site.com",
                'id' => 'signupFormEmail'
            ], 
            'label' => false
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Le mot de passe doit être identique.',
            'label' => false,
            'mapped' => false,
            'required' => true,
            'first_options'  => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Répéter le mot de passe'],
            'attr' => [
                'class' =>'form-control',
                'placeholder' =>"6+ characteres requis",
                'autocomplete' => 'new-password'],
                'constraints' => [
                new NotBlank([
                    'message' => 'Entrez un mot de passe svp',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} characteres',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('agreeTerms', CheckboxType::class, [
                'label' => false,
                'mapped' => false,
                'attr' => [
                    'class' =>'form-check-input', 
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
    //     ->add('plainPassword', PasswordType::class, [
    //             // instead of being set onto the object directly,
    //             // this is read and encoded in the controller
    //             'mapped' => false,
    //             'attr' => ['autocomplete' => 'new-password']
    //         ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
