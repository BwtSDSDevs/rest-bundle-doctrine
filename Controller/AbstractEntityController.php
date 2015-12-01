<?php


namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\Repository\OrmEntityRepository;
use Dontdrinkandroot\UtilsBundle\Controller\EntityControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractEntityController extends BaseController implements EntityControllerInterface
{

    protected $routePrefix = null;

    protected $pathPrefix = null;

    public function listAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perpage', 10);
        $paginatedEntities = $this->getRepository()->findPaginatedBy($page, $perPage);
        $entities = $paginatedEntities->getResults();
        $pagination = $paginatedEntities->getPagination();
        $view = $this->view($entities);
        $this->addPaginationHeaders($pagination, $view);

        return $this->handleView($view);
    }

    public function detailAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $view = $this->view($entity);

        return $this->handleView($view);
    }

    /**
     * @param Request           $request
     * @param mixed|null|string $id
     *
     * @return Response
     */
    public function editAction(Request $request, $id = null)
    {
        $create = (null === $id);

        $entity = $this->deserializeRequestContent($request, $this->getEntityClass());
        $errors = $this->validate($entity);
        if (count($errors) > 0) {
            $view = $this->view($errors, 400);

            return $this->handleView($view);
        }

        if ($create) {
            $entity = $this->getRepository()->persist($entity);
        } else {
            $entity = $this->getRepository()->merge($entity);
        }

        $status = $create ? 201 : 200;

        $view = $this->view($entity, $status);

        if ($create) {
            $view->setHeader(
                'Location',
                $this->generateUrl($this->getDetailRoute(), ['id' => $entity->getId()], true)
            );
        }

        return $this->handleView($view);
    }

    public function deleteAction(Request $request, $id)
    {
        $entity = $this->fetchEntity($id);
        $this->getRepository()->remove($entity);
        $view = $this->view();
        $view->setStatusCode(204);

        return $this->handleView($view);
    }

    /**
     * @param string|null $routePrefix
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;
    }

    /**
     * @param string|null $pathPrefix
     */
    public function setPathPrefix($pathPrefix)
    {
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * @param $id
     *
     * @return EntityInterface
     */
    protected function fetchEntity($id)
    {
        $entity = $this->getRepository()->find($id);
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    /**
     * @return OrmEntityRepository
     */
    protected function getRepository()
    {
        return $this->getDoctrine()->getRepository($this->getEntityClass());
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePrefix()
    {
        if (null !== $this->routePrefix) {
            return $this->routePrefix;
        }

        list($bundle, $entityName) = $this->extractBundleAndEntityName($this->getEntityClass());

        $prefix = str_replace('Bundle', '', $bundle);
        $prefix = $prefix . '.' . $entityName;
        $prefix = str_replace('\\', '.', $prefix);
        $prefix = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $prefix));

        return $prefix . '.rest';
    }

    /**
     * {@inheritdoc}
     */
    public function getPathPrefix()
    {
        if (null !== $this->pathPrefix) {
            return $this->pathPrefix;
        }

        list($bundle, $entityName) = $this->extractBundleAndEntityName($this->getEntityClass());

        return '/' . strtolower($entityName) . '/';
    }

    protected function extractBundleAndEntityName($entityClass)
    {
        $entityClass = $this->getEntityClass();
        $parts = explode(':', $entityClass);
        if (2 !== count($parts)) {
            throw new \Exception(sprintf('Expecting entity class to be "Bundle:Entity", %s given', $entityClass));
        }

        return $parts;
    }

    /**
     * @return string
     */
    protected function getDetailRoute()
    {
        return $this->getRoutePrefix() . ".detail";
    }

    /**
     * @return string
     */
    protected abstract function getEntityClass();
}
