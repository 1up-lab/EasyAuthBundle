<?php

declare(strict_types=1);

namespace mikemeier\EasyAuthBundle\Auth;

use FOS\UserBundle\Model\UserInterface;
use mikemeier\EasyAuthBundle\Form\LoginType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\Translator;

class EasyAuth implements EasyAuthInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

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
     * @var Translator
     */
    protected $translator;

    /**
     * EasyAuth constructor.
     *
     * @param RequestStack              $requestStack
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param TokenStorageInterface     $tokenStorage
     * @param FormFactoryInterface      $formFactory
     * @param Translator                $translator
     */
    public function __construct(
        RequestStack $requestStack,
        CsrfTokenManagerInterface $csrfTokenManager,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        Translator $translator
    ) {
        $this->requestStack = $requestStack;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->tokenStorage = $tokenStorage;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->setInformation();

        $this->request = $this->requestStack->getCurrentRequest();
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            return null;
        }

        return $user instanceof UserInterface ? $user : null;
    }

    public function logout(): void
    {
        $this->tokenStorage->setToken(null);
        $this->request->getSession()->invalidate();
    }

    /**
     * @param string            $failurePath
     * @param string            $targetPath
     * @param FormTypeInterface $loginType
     * @param array             $data
     * @param array             $options
     *
     * @return FormInterface
     */
    public function getLoginForm($failurePath = 'login', $targetPath = 'index', FormTypeInterface $loginType = null, array $data = [], array $options = [])
    {
        $defaultData = [
            '_csrf_token' => $this->getCsrfToken(),
            '_username' => $this->getLastUsername(),
            '_target_path' => $this->request->getSession()->get('_security.main.target_path', $targetPath),
            '_failure_path' => $failurePath,
        ];

        $data = array_merge($defaultData, $data);
        $loginForm = $this->createForm($loginType ?: new LoginType(), $data, $options);

        if ($authError = $this->getAuthenticationError()) {
            $loginForm->addError(new FormError($this->translator->trans($authError)));
        }

        return $loginForm;
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->csrfTokenManager->getToken('authenticate');
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
     * @return EasyAuth
     */
    public function removeInformation()
    {
        $session = $this->request->getSession();
        $session->remove(Security::LAST_USERNAME);
        $session->remove(Security::AUTHENTICATION_ERROR);

        return $this;
    }

    protected function setInformation(): void
    {
        $request = $this->request;
        $session = $request->getSession();

        $key = Security::AUTHENTICATION_ERROR;
        $error = null;

        if ($request->attributes->has($key)) {
            $error = $request->attributes->get($key);
        } elseif ($session->has($key)) {
            $error = $session->get($key);
        }

        if ($error && $error instanceof \Exception) {
            $error = $error->getMessage();
        }

        $this->error = $error;
        $this->lastUsername = $session->get(Security::LAST_USERNAME);
    }

    /**
     * @param string|FormTypeInterface $type
     * @param mixed
     * @param array
     * @param mixed|null $data
     *
     * @return FormInterface
     */
    protected function createForm($type, $data = null, array $options = [])
    {
        return $this->formFactory->create($type, $data, $options);
    }
}
