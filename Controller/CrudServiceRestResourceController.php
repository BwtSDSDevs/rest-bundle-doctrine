<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Service\CrudServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
abstract class CrudServiceRestResourceController extends AbstractRestResourceController
{
    /**
     * {@inheritdoc}
     */
    protected function listEntities(int $page = 1, int $perPage = 50)
    {
        return $this->getService()->findAllPaginated($page, $perPage);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchEntity($id)
    {
        $entity = $this->getService()->find($id);
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($entity)
    {
        return $this->getService()->create($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function updateEntity($entity)
    {
        return $this->getService()->update($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeEntity($entity)
    {
        $this->getService()->remove($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function listSubresource($entity, string $property, int $page = 1, int $perPage = 50)
    {
        return $this->getService()->findAssociationPaginated($entity, $property, $page, $perPage);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAssociation($parent, string $subresource)
    {
        return $this->getService()->createAssociation($parent, $subresource);
    }

    /**
     * {@inheritdoc}
     */
    protected function createSubResource($parent, $subresource, $entity)
    {
        return $this->getService()->create($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function addAssociation($parent, string $subresource, $subId)
    {
        $this->getService()->addAssociation($parent, $subresource, $subId);
    }

    /**
     * {@inheritdoc}
     */
    protected function removeAssociation($parent, string $subresource, $subId = null)
    {
        $this->getService()->removeAssociation($parent, $subresource, $subId);
    }

    protected function getServiceId()
    {
        return $this->getCurrentRequest()->attributes->get('_service');
    }

    /**
     * @return CrudServiceInterface
     */
    abstract protected function getService();
}
