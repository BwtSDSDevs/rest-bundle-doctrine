<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Repository\AccessTokenRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessTokenService implements AccessTokenServiceInterface
{
    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var string
     */
    private $accessTokenClass;

    /**
     * @var string
     */
    private $defaultExpirationDuration = '+1 month';

    /**
     * @var AuthenticationProviderInterface
     */
    private $authenticationManager;

    /**
     * @var string
     */
    private $authenticationProviderKey;

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $accessTokenClass,
        AuthenticationManagerInterface $authenticationManager,
        $authenticationProviderKey
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->accessTokenClass = $accessTokenClass;
        $this->authenticationManager = $authenticationManager;
        $this->authenticationProviderKey = $authenticationProviderKey;
    }

    /**
     * {@inheritdoc}
     */
    public function createAcessToken($username, $password)
    {
        $usernamePasswordToken = new UsernamePasswordToken($username, $password, $this->authenticationProviderKey);
        $token = $this->authenticationManager->authenticate($usernamePasswordToken);
        $accessToken = $this->generateAndSaveAccessToken($token->getUser());

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->accessTokenRepository->findUserByToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function listByUser(UserInterface $user)
    {
        return $this->accessTokenRepository->findBy(['user' => $user]);
    }

    private function generateAndSaveAccessToken($user)
    {
        $token = bin2hex(random_bytes(32));
        /** @var AccessToken $accessToken */
        $accessToken = new $this->accessTokenClass;
        $accessToken->setToken($token);
        $accessToken->setUser($user);
        $accessToken->setExpiry(new \DateTime($this->defaultExpirationDuration));

        $accessToken = $this->accessTokenRepository->persist($accessToken);

        return $accessToken;
    }

    /**
     * @return int
     */
    public function cleanUpExpiredTokens()
    {
        $accessTokens = $this->accessTokenRepository->findExpiredTokens();
        foreach ($accessTokens as $accessToken) {
            $this->accessTokenRepository->remove($accessToken);
        }

        return count($accessTokens);
    }

    /**
     * @return string
     */
    public function getDefaultExpirationDuration()
    {
        return $this->defaultExpirationDuration;
    }

    /**
     * @param string $defaultExpirationDuration
     */
    public function setDefaultExpirationDuration($defaultExpirationDuration)
    {
        $this->defaultExpirationDuration = $defaultExpirationDuration;
    }
}
