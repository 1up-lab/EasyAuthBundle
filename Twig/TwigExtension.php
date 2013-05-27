<?php

namespace mikemeier\EasyAuthBundle\Twig;

use mikemeier\EasyAuthBundle\Auth\EasyAuthInterface;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var EasyAuthInterface
     */
    protected $easyAuth;

    /**
     * @param EasyAuthInterface $easyAuth
     */
    public function __construct(EasyAuthInterface $easyAuth)
    {
        $this->easyAuth = $easyAuth;
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
        return $this->easyAuth;
    }

    /**
     * @return string The extension name
     */
    public function getName()
    {
        'mikemeier_easyauth_twigextension';
    }
}