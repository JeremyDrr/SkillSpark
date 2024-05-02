<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration('First Name', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('lastName', TextType::class, $this->getConfiguration('Last Name', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('email', EmailType::class, $this->getConfiguration('Email address', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('picture', TextType::class, $this->getConfiguration('Picture', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('introduction', TextType::class, $this->getConfiguration('Introduction', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
