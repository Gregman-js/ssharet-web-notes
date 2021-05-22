<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Room;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['attr' => ['maxlength' => 25, 'class' => 'form-control', 'autocomplete' => 'off']])
            ->add('shortDescription', null, ['attr' => ['maxlength' => 40, 'class' => 'form-control', 'autocomplete' => 'off']])
            ->add('password', PasswordType::class, ['required' => false, 'attr' => ['maxlength' => 40, 'class' => 'form-control', 'autocomplete' => 'off']])
            ->add('includeImages', CheckboxType::class, ['required' => false])
            ->add('includeFiles', CheckboxType::class, ['required' => false])
            ->add('url', null, ['attr' => ['class' => 'form-control url-suffix-inp', 'maxlength' => 25, 'autocomplete' => 'off']])
            ->add('guestAccess', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'checkbox-switch']])
            ->add('guestEdit', CheckboxType::class, ['required' => false, 'attr' => ['class' => 'checkbox-switch']])
            ->add('finish', SubmitType::class, ['label' => 'Finish', 'attr' => ['class' => 'btn btn-finish btn-fill btn-success', 'style' => 'display: none;']])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}