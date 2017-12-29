<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParserInterface;
use Dontdrinkandroot\Service\CrudServiceInterface;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DoctrineRestResourceController extends CrudServiceRestResourceController
{
    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RestRequestParserInterface
     */
    private $requestParser;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        RestRequestParserInterface $requestParser,
        Normalizer $normalizer,
        ValidatorInterface $validator,
        RequestStack $requestStack,
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->requestParser = $requestParser;
        $this->requestStack = $requestStack;
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
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
    protected function getNormalizer()
    {
        return $this->normalizer;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidator()
    {
        return $this->validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestParser()
    {
        return $this->requestParser;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationChecker(): ?AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
    }

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }
}
