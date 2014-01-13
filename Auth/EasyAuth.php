<?php

namespace mikemeier\EasyAuthBundle\Auth;

use mikemeier\EasyAuthBundle\Form\LoginType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class EasyAuth implements EasyAuthInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfProvider;

    /**
     * @var string
     */
    protected $lastUsername;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param Request $request
     * @param CsrfTokenManagerInterface $csrfProvider
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Request $request,
        CsrfTokenManagerInterface $csrfProvider,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator
    ){
        $this->request = $request;
        $this->csrfProvicer = $csrfProvider;
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->setInformation();
    }

    /**
     * @return string|null
     */
    public function getLastUsername()
    {
        return $this->lastUsername;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->csrfProvicer->getToken('authenticate');
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        if(null === $token = $this->securityContext->getToken()){
            return null;
        }

        if(!is_object($user = $token->getUser())){
            return null;
        }

        return $user instanceof UserInterface ? $user : null;
    }

    public function logout()
    {
        $this->securityContext->setToken(null);
        $this->request->getSession()->invalidate();
    }

    /**
     * @param string $failurePath
     * @param string $targetPath
     * @param FormTypeInterface $loginType
     * @param array $data
     * @param array $options
     * @return FormInterface
     */
    public function getLoginForm($failurePath = 'login', $targetPath = 'index', FormTypeInterface $loginType = null, array $data = array(), array $options = array())
    {
        $defaultData = array(
            '_csrf_token' => $this->getCsrfToken(),
            '_username' => $this->getLastUsername(),
            '_target_path' => $this->request->getSession()->get('_security.main.target_path', $targetPath),
            '_failure_path' => $failurePath
        );

        $data = array_merge($defaultData, $data);
        $loginForm = $this->createForm($loginType?:new LoginType(), $data, $options);

        if($authError = $this->getAuthenticationError()){
            $loginForm->addError(new FormError($this->translator->trans($authError)));
        }

        return $loginForm;
    }

    /**
     * @return EasyAuth
     */
    public function removeInformation()
    {
        $session = $this->request->getSession();
        $session->remove(SecurityContextInterface::LAST_USERNAME);
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        return $this;
    }

    protected function setInformation()
    {
        $request = $this->request;
        $session = $request->getSession();

        $key = SecurityContextInterface::AUTHENTICATION_ERROR;
        $error = null;

        if($request->attributes->has($key)){
            $error = $request->attributes->get($key);
        }elseif($session->has($key)){
            $error = $session->get($key);
        }

        if($error && $error instanceof \Exception){
            $error = $error->getMessage();
        }

        $this->error = $error;
        $this->lastUsername = $session->get(SecurityContextInterface::LAST_USERNAME);
    }

    /**
     * @param string|FormTypeInterface $type
     * @param mixed
     * @param array
     * @return FormInterface
     */
    protected function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }
}
