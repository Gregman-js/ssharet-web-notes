<?php
// AcmeBundle\Security\LoginSuccessHandler.php

namespace UserBundle\Security;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

    protected $router;
    protected $authorizationChecker;
    private $container;

    public function __construct(Router $router, AuthorizationChecker $authorizationChecker, Container $container) {
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->container = $container;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {

        $response = null;

        $response = new RedirectResponse($this->router->generate($this->container->get('security.token_storage')->getToken()->getUser()->getStartPage()));

        return $response;
    }

}