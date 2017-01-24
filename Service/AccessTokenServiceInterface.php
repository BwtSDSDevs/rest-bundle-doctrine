<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Entity\AccessToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenServiceInterface
{
    public function createAccessToken(string $username, string $password): AccessToken;

    public function createAccessTokenForUser(UserInterface $user): AccessToken;

    public function findUserByToken(string $token): ?UserInterface;

    public function cleanUpExpiredTokens(): int;

    public function listByUser(UserInterface $user): array;
}
