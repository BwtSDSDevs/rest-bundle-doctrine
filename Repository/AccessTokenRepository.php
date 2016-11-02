<?php

namespace Dontdrinkandroot\RestBundle\Repository;

use Doctrine\ORM\Query;
use Dontdrinkandroot\Repository\OrmEntityRepository;
use Dontdrinkandroot\RestBundle\Entity\AccessToken;

class AccessTokenRepository extends OrmEntityRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->getTransactionManager()->transactional(
            function () use ($token) {
                /** @var AccessToken $accessToken */
                $accessToken = $this->createFindUserByTokenQuery($token)->getOneOrNullResult();
                if (null === $accessToken) {
                    return null;
                }

                if ($this->isExpired($accessToken)) {
                    $this->remove($accessToken);

                    return null;
                }

                return $accessToken->getUser();
            }
        );
    }

    /**
     * @param string $token
     *
     * @return Query
     */
    protected function createFindUserByTokenQuery($token)
    {
        $queryBuilder = $this->createQueryBuilder('accessToken');
        $queryBuilder->where('accessToken.token = :token');
        $queryBuilder->setParameter('token', $token);

        return $queryBuilder->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function findExpiredTokens()
    {
        $queryBuilder = $this->createQueryBuilder('token');
        $queryBuilder->where('token.expiry < :now');
        $queryBuilder->setParameter('now', new \DateTime());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param AccessToken $accessToken
     *
     * @return bool
     */
    private function isExpired(AccessToken $accessToken)
    {
        if (null !== $accessToken->getExpiry()) {
            return $accessToken->getExpiry() < new \DateTime();
        }

        return false;
    }
}
