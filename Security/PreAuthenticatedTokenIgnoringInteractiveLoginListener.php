<?php

namespace Dontdrinkandroot\RestBundle\Security;

use FOS\UserBundle\Security\InteractiveLoginListener;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class PreAuthenticatedTokenIgnoringInteractiveLoginListener extends InteractiveLoginListener
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if (!$event->getAuthenticationToken() instanceof PreAuthenticatedToken) {
            parent::onSecurityInteractiveLogin($event);
        }
    }
}
