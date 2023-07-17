<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    private $jsonArrayTransformer;

    public function __construct(JsonArrayTransformer $jsonArrayTransformer)
    {
        $this->jsonArrayTransformer = $jsonArrayTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'border flex w-2/6 mx-auto'
                ]
            ])
            ->add('roles')
            ->add('password', TextType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'class' => 'border flex w-2/6 mx-auto'
                ]
            ])
            ->add('isVerified');

        $builder->get('roles')->addModelTransformer($this->jsonArrayTransformer);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
