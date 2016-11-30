<?php

namespace Dontdrinkandroot\RestBundle\Security;

use FOS\UserBundle\EventListener\LastLoginListener;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class PreAuthenticationIgnoringLastLoginListener extends LastLoginListener
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN       => 'onImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN            => 'onSecurityInteractiveLogin',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSucess'
        ];
    }

    public function onAuthenticationSucess(AuthenticationEvent $event)
    {
        $authenticationToken = $event->getAuthenticationToken();
        if ($authenticationToken instanceof UsernamePasswordToken) {
            $user = $authenticationToken->getUser();

            if ($user instanceof UserInterface) {
                $user->setLastLogin(new \DateTime());
                $this->userManager->updateUser($user);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $authenticationToken = $event->getAuthenticationToken();
        if (!$authenticationToken instanceof PreAuthenticatedToken) {
            parent::onSecurityInteractiveLogin($event);
        }
    }
}
