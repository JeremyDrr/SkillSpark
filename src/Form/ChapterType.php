<?php

namespace App\Form;

use App\Entity\Chapter;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration('Title', '', [
                'attr' => [
                    'class' => 'form-control mb-3'
                ]
            ]))
            ->add('content', TextareaType::class, $this->getConfiguration('Content', '', [
                'attr' => [
                    'class' => 'mb-5 form-control quill-editor',
                    'rows' => 3
                ],

            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
