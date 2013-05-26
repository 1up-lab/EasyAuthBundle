<?php

namespace mikemeier\EasyAuthBundle\Form;

use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class AbstractType extends BaseType
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'forms'
        ));
    }
}