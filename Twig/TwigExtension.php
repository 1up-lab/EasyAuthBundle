<?php

namespace mikemeier\EasyAuthBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use mikemeier\EasyAuthBundle\Auth\EasyAuthInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions(){
        return array(
            'getEasyAuth' => new \Twig_Function_Method($this, 'getEasyAuth')
        );
    }

    /**
     * @return EasyAuthInterface
     */
    public function getEasyAuth()
    {
        return $this->container->get('mikemeier.easyauth');
    }

    /**
     * @return string The extension name
     */
    public function getName()
    {
        'mikemeier_easyauth_twigextension';
    }
}