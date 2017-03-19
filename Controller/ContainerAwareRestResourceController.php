<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Service\CrudServiceInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ContainerAwareRestResourceController extends CrudServiceRestResourceController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return CrudServiceInterface
     */
    protected function getService(): CrudServiceInterface
    {
        $serviceId = $this->getServiceId();
        if (null === $serviceId) {
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
        } else {
            /** @var CrudServiceInterface $service */
            $service = $this->container->get($serviceId);

            return $service;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizer()
    {
        return $this->container->get('ddr_rest.normalizer');
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidator()
    {
        return $this->container->get('validator');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestParser()
    {
        return $this->container->get('ddr_rest.parser.request');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequestStack()
    {
        return $this->container->get('request_stack');
    }

    /**
     * {@inheritdoc}
     */
    protected function getMetadataFactory()
    {
        return $this->container->get('ddr_rest.metadata.factory');
    }

    /**
     * {@inheritdoc}
     */
    protected function getPropertyAccessor()
    {
        return $this->container->get('property_accessor');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationChecker()
    {
        return $this->container->get('security.authorization_checker');
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
