<?php

namespace Dontdrinkandroot\RestBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Dontdrinkandroot\RestBundle\Entity\AccessToken;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class AccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->getEntityManager()->transactional(
            function () use ($token) {
                /** @var AccessToken $accessToken */
                $accessToken = $this->createFindUserByTokenQuery($token)->getOneOrNullResult();
                if (null === $accessToken) {
                    return null;
                }

                if ($this->isExpired($accessToken)) {
                    $this->getEntityManager()->remove($accessToken);

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

    /**
     * {@inheritdoc}
     */
    public function persist(AccessToken $accessToken): AccessToken
    {
        $this->getEntityManager()->persist($accessToken);
        $this->getEntityManager()->flush($accessToken);

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(AccessToken $accessToken)
    {
        $this->getEntityManager()->remove($accessToken);
        $this->getEntityManager()->flush($accessToken);
    }
}
