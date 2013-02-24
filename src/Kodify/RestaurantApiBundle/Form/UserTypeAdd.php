<?php

namespace Kodify\RestaurantApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;

class UserTypeAdd extends AbstractType
{
    /**
     * Form builder
     * @param FormBuilder $builder the form builder
     * @param array       $options options for this form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('username', 'text', array('required' => true))
            ->add('email', 'email', array('required' => true))
            ->add(
                'password',
                'repeated',
                array(
                    'required' => true,
                    'type' => 'password',
                    'first_name' => 'password',
                    'second_name' => 'password_confirm',
                    'invalid_message' => 'Passwords should be equals'
                )
            );
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'csrf_protection' => false            
        );
    }


    /**
     * Get the name of the form
     * @return string
     */
    public function getName()
    {
        return 'User';
    }
}