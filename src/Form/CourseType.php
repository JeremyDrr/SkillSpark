<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Course;
use App\Entity\Level;
use App\Entity\User;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration('Title', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('introduction', TextType::class, $this->getConfiguration('Introduction', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('thumbnail', UrlType::class, $this->getConfiguration('Thumbnail', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ],
            ]))
            ->add('price', MoneyType::class, $this->getConfiguration('Price', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
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
            ->add('chapters', CollectionType::class, $this->getConfiguration('', '', [
                'attr' => [
                    'class' => 'mb-3 form-control'
                ],
                'required' => false,
                'entry_type' => ChapterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'label' => false,
                ],
            ]))
            ->add('category', EntityType::class, $this->getConfiguration('', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ],
                'class' => Category::class,
                'required' => true,
                'choice_label' => 'name',

            ]))
            ->add('active', CheckboxType::class, $this->getConfiguration('Active', '', [
                'attr' => [
                    'class' => 'form-check mb-3'
                ],
                'required' => false
            ]))

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
