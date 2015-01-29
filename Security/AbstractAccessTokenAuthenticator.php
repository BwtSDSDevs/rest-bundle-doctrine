<?php


namespace Dontdrinkandroot\RestBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

abstract class AbstractAccessTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    const DEFAULT_TOKEN_QUERY_PARAMETER_NAME = 'token';
    const DEFAULT_TOKEN_HEADER_NAME = 'X-Access-Token';

    /** @var string */
    protected $tokenQueryParameterName = self::DEFAULT_TOKEN_QUERY_PARAMETER_NAME;

    /** @var string */
    protected $tokenHeaderName = self::DEFAULT_TOKEN_HEADER_NAME;

    /**
     * @inheritdoc
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $tokenString = $token->getCredentials();

        $user = $this->findUserByToken($tokenString);

        if (null === $user) {
            throw new AuthenticationException('Invalid Access Token');
        }

        return new PreAuthenticatedToken(
            $user,
            $tokenString,
            $providerKey,
            $user->getRoles()
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function createToken(Request $request, $providerKey)
    {
        $token = $request->query->get($this->getTokenQueryParameterName());
        if (null === $token) {
            $token = $request->headers->get($this->getTokenHeaderName());
        }

        if (null === $token || 'null' === $token) {
            return null;
        }

        return new PreAuthenticatedToken(
            'anon.',
            $token,
            $providerKey
        );
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed.", 403);
    }

    /**
     * @return string
     */
    public function getTokenQueryParameterName()
    {
        return $this->tokenQueryParameterName;
    }

    /**
     * @param $tokenQueryName
     */
    public function setTokenQueryParameterName($tokenQueryName)
    {
        $this->tokenQueryParameterName = $tokenQueryName;
    }

    /**
     * @return string
     */
    public function getTokenHeaderName()
    {
        return $this->tokenHeaderName;
    }

    /**
     * @param $tokenHeaderName
     */
    public function setTokenHeaderName($tokenHeaderName)
    {
        $this->tokenHeaderName = $tokenHeaderName;
    }

    /**
     * @param $token
     *
     * @return UserInterface|null
     */
    protected abstract function findUserByToken($token);
}