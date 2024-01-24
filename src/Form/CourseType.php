<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Level;
use App\Entity\User;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration('Title', '', [
                'attr' => [
                    'class' => 'form-control'
                ]
            ]))
            ->add('introduction', TextType::class, $this->getConfiguration('Introduction', '', [
                'attr' => [
                    'class' => 'form-control'
                ]
            ]))
            ->add('thumbnail', TextType::class, $this->getConfiguration('Thumbnail', '', [
                'attr' => [
                    'class' => 'form-control'
                ]
            ]))
            ->add('price', MoneyType::class, $this->getConfiguration('Price', '', [
                'attr' => [
                    'class' => 'form-control'
                ],
                'currency'=>''
            ]))
            ->add('level', EntityType::class, [
                'class' => Level::class,
'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
