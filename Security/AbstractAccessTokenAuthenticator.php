<?php

namespace Dontdrinkandroot\RestBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

abstract class AbstractAccessTokenAuthenticator
    implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    const DEFAULT_TOKEN_QUERY_PARAMETER_NAME = 'token';
    const DEFAULT_TOKEN_HEADER_NAME = 'X-Access-Token';

    /**
     * @var string
     */
    protected $tokenQueryParameterName = self::DEFAULT_TOKEN_QUERY_PARAMETER_NAME;

    /**
     * @var string
     */
    protected $tokenHeaderName = self::DEFAULT_TOKEN_HEADER_NAME;

    /**
     * If set to true an AuthenticationException will be thrown if no Access Token was found. Otherwise if will simply
     * continue with other authentication methods.
     *
     * @var bool
     */
    protected $tokenRequired = false;

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $tokenString = $token->getCredentials();

        $user = $this->findUserByToken($tokenString);

        if (null === $user || !$user instanceof UserInterface) {
            throw new AuthenticationException('Invalid Access Token');
        }

        $userRoles = $user->getRoles();
        $userRoles[] = 'ROLE_REST_API';

        return new PreAuthenticatedToken(
            $user,
            $tokenString,
            $providerKey,
            $userRoles
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $providerKey)
    {
        $token = $request->query->get($this->getTokenQueryParameterName());
        if (null === $token) {
            $token = $request->headers->get($this->getTokenHeaderName());
        }

        if (null === $token || 'null' === $token) {
            if ($this->tokenRequired) {
                throw $this->createTokenMissingException();
            } else {
                return null;
            }
        }

        return new PreAuthenticatedToken(
            'anon.',
            $token,
            $providerKey
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new BadCredentialsException($exception->getMessage(), Response::HTTP_FORBIDDEN, $exception);
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
     * @return bool
     */
    public function isTokenRequired()
    {
        return $this->tokenRequired;
    }

    /**
     * @param bool $tokenRequired
     */
    public function setTokenRequired($tokenRequired)
    {
        $this->tokenRequired = $tokenRequired;
    }

    /**
     * @return AuthenticationException
     */
    protected function createTokenMissingException()
    {
        return new BadCredentialsException('No Access Token found');
    }

    /**
     * @param $token
     *
     * @return UserInterface|null
     */
    protected abstract function findUserByToken($token);
}
