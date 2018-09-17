<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\Service\CrudServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DoctrineRestResourceController extends AbstractCrudServiceRestResourceController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        RestMetadataFactory $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        parent::__construct(
            $validator,
            $requestStack,
            $metadataFactory,
            $propertyAccessor,
            $serializer
        );
        $this->entityManager = $entityManager;
    }

    /**
     * @return CrudServiceInterface
     */
    protected function getService(): CrudServiceInterface
    {
        $entityClass = $this->getEntityClass();
        if (null === $entityClass) {
            throw new \RuntimeException('No service or entity class given');
        }
        $entityManager = $this->getEntityManager();
        $repository = $entityManager->getRepository($entityClass);
        if (!$repository instanceof CrudServiceInterface) {
            throw new \RuntimeException(
                'Your Entity Repository needs to be an instance of ' . CrudServiceInterface::class . '.'
            );
        }

        return $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }
}
