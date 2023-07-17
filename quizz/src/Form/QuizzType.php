<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class QuizzType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['choices'];
        $builder
            ->add('id_categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => $choices,
                'attr' => [
                    'class' => 'border flex w-2/6 mx-auto'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'border flex w-2/6 mx-auto'
                ]
            ]);
            for ($i = 1; $i <= 10; $i++) {
                $builder
                    ->add('question_'.$i, TextType::class, [
                        'label' => 'Question '.$i,
                        'attr' => [
                            'class' => 'border flex w-2/6 mx-auto hover:bg-gray-200'
                        ]
                    ])
                    ->add('reponse_'.$i.'_1', TextType::class, [
                        'label' => 'Réponse 1 pour la question '.$i,
                        'attr' => [
                            'class' => 'border flex w-2/6 mx-auto hover:bg-gray-200'
                        ]
                    ])
                    ->add('reponse_'.$i.'_2', TextType::class, [
                        'label' => 'Réponse 2 pour la question '.$i,
                        'attr' => [
                            'class' => 'border flex w-2/6 mx-auto hover:bg-gray-200'
                        ]
                    ])
                    ->add('reponse_'.$i.'_3', TextType::class, [
                        'label' => 'Réponse 3 pour la question '.$i,
                        'attr' => [
                            'class' => 'border flex w-2/6 mx-auto hover:bg-gray-200'
                        ]
                    ])
                    ->add('reponse_expected_'.$i, ChoiceType::class, [
                        'label' => 'Réponse attendue pour la question '.$i,
                        'choices' => [
                            'Réponse 1' => 'reponse_'.$i.'_1',
                            'Réponse 2' => 'reponse_'.$i.'_2',
                            'Réponse 3' => 'reponse_'.$i.'_3',
                        ],
                        'attr' => [
                            'class' => 'border flex w-2/6 mx-auto my-4 hover:bg-gray-200'
                        ],
                        'multiple' => false,
                        'required' => true
                    ]);
                    
            }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'choices' => array(),
        ]);
    }
}
