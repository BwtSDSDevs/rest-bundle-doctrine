<?php

namespace Dontdrinkandroot\RestBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\Query;
use Dontdrinkandroot\Repository\TransactionManager;
use Dontdrinkandroot\RestBundle\Entity\AccessToken;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class AccessTokenRepository extends EntityRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var TransactionManager
     */
    private $transactionManager;

    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->transactionManager = new TransactionManager($em);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByToken($token)
    {
        return $this->transactionManager->transactional(
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

    private function isExpired(AccessToken $accessToken): bool
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
        $this->transactionManager->transactional(
            function () use ($accessToken) {
                $this->getEntityManager()->persist($accessToken);
            }
        );

        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(AccessToken $accessToken)
    {
        $this->transactionManager->transactional(
            function () use ($accessToken) {
                $this->getEntityManager()->remove($accessToken);
            }
        );
    }
}
