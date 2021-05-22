<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', null, ['disabled' => true])
        ->add('email', null, ['disabled' => true])
        ->add('activeColor', ChoiceType::class, [
            'label' => "Navigation Color",
            'choices'  => [
                'Aqua' => 'primary',
                'Blue' => 'info',
                'Green' => 'success',
                'Yellow' => 'warning',
                'Red' => 'danger',
            ]
        ])
        ->add('firstName', null, ['label' => "First Name"])
        ->add('lastName', null, ['label' => "Last Name"])
        ->add('aboutMe', TextareaType::class, ['label' => "About Me", 'attr' => ['class' => 'form-control textarea']])
        ->add('startPage', ChoiceType::class, [
            'label' => "After login",
            'choices'  => [
                'Dashboard' => 'panel',
                'Rooms' => 'panel_room',
                'User profile' => 'panel_profile'
            ]
        ])
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user_picture';
    }


}
