<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordUpdateType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, $this->getConfiguration('Current Password', '', [
                'attr' => [
                    'class' => 'form-control mb-3',
                ]
            ]))
            ->add('newPassword', PasswordType::class, $this->getConfiguration('New Password', '', [
                'attr' => [
                    'class' => 'form-control mb-3',
                ]
            ]))
            ->add('confirmPassword', PasswordType::class, $this->getConfiguration('Confirm Password', '', [
                'attr' => [
                    'class' => 'form-control mb-3',
                ]
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
