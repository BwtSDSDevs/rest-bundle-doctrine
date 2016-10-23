<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Repository\AccessTokenRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

// TODO: Make user provider configurable
class AccessTokenService implements AccessTokenServiceInterface
{
    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var string
     */
    private $accesTokenClass;

    /**
     * @var string
     */
    private $defaultExpirationDuration = '+1 month';

    public function __construct(
        AccessTokenRepositoryInterface $accessTokenRepository,
        $accesTokenClass,
        UserProviderInterface $userProvider,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;
        $this->accesTokenClass = $accesTokenClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createAcessToken($username, $password)
    {
        $user = $this->userProvider->loadUserByUsername($username);
        $encoder = $this->encoderFactory->getEncoder($user);

        if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            $accessToken = $this->generateAndSaveAccessToken($user);

            return $accessToken;
        }

        throw new AuthenticationException();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->accessTokenRepository->findUserByToken($token);
    }

    private function generateAndSaveAccessToken($user)
    {
        $token = bin2hex(random_bytes(32));
        /** @var AccessToken $accessToken */
        $accessToken = new $this->accesTokenClass;
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