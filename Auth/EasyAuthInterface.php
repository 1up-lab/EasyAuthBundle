<?php

namespace mikemeier\EasyAuthBundle\Auth;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;

interface EasyAuthInterface
{
    /**
     * @return string|null
     */
    public function getLastUsername();

    /**
     * @return string|null
     */
    public function getAuthenticationError();

    /**
     * @return string
     */
    public function getCsrfToken();

    /**
     * @return UserInterface
     */
    public function getUser();

    public function logout();

    /**
     * @param string $failurePath
     * @param string $targetPath
     * @param FormTypeInterface $loginType
     * @param array $data
     * @param array $options
     * @return FormInterface
     */
    public function getLoginForm($failurePath = 'login', $targetPath = 'index', FormTypeInterface $loginType = null, array $data = array(), array $options = array());

    /**
     * @return EasyAuthInterface
     */
    public function removeInformation();
}
