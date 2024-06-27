<?php

namespace App\Form;

use App\Entity\ResetPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, $this->getConfiguration("Password", "", [
                'attr' => [
                    'class' => 'form-control bg-transparent mb-3',
                ]
            ]))
            ->add('confirmPassword', PasswordType::class, $this->getConfiguration("Confirm Password", "", [
                'attr' => [
                    'class' => 'form-control bg-transparent mb-3',
                ]
            ]))
            ->add('token', HiddenType::class, $this->getConfiguration("Token", "Token", [
                'attr' => [
                    'class' => 'form-control ',
                ],
                'data' => $options['tokenValue'],
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResetPassword::class,
            'tokenValue' => 0000,
        ]);
    }
}
