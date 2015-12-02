<?php


namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\Repository\OrmEntityRepository;
use Dontdrinkandroot\Utils\StringUtils;
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
        $user = $this->getUser();
        $this->checkListActionAuthorization($user);

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

        $user = $this->getUser();
        $this->checkDetailActionAuthorization($user, $entity);


        $view = $this->view($entity);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param mixed|null|string $id
     *
     * @return Response
     */
    public function editAction(Request $request, $id = null)
    {
        $create = (null === $id);

        $user = $this->getUser();
        $originalEntity = null;
        if ($create) {
            $this->checkCreateActionAuthorization($user);
        } else {
            $originalEntity = $this->fetchEntity($id);
            $this->checkUpdateActionAuthorization($user, $originalEntity);
        }

        $entity = $this->deserializeRequestContent($request, $this->getEntityClass());
        $entity = $this->postProcessDeserializedEntity($entity, $originalEntity);

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
        $user = $this->getUser();
        $entity = $this->fetchEntity($id);
        $this->checkDeleteActionAuthorization($user, $entity);

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

        list($bundle, $entityName) = $this->extractBundleAndEntityName();

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

        list($bundle, $entityName) = $this->extractBundleAndEntityName();

        return '/' . strtolower($entityName) . '/';
    }

    protected function extractBundleAndEntityName()
    {
        $shortName = $this->getEntityShortName();
        $parts = explode(':', $shortName);
        if (2 !== count($parts)) {
            throw new \Exception(sprintf('Expecting entity class to be "Bundle:Entity", %s given', $shortName));
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
    protected function getEntityShortName()
    {
        $entityClass = $this->getEntityClass();
        $entityClassParts = explode('\\', $entityClass);

        $bundle = $this->findBundle($entityClassParts);
        $className = $entityClassParts[count($entityClassParts) - 1];

        $shortName = $bundle . ':' . $className;

        return $shortName;
    }

    private function findBundle(array $entityClassParts)
    {
        foreach ($entityClassParts as $part) {
            if (StringUtils::endsWith($part, 'Bundle')) {
                return $part;
            }
        }

        throw new \RuntimeException('No Bundle found in namespace');
    }

    /**
     * @param EntityInterface $entity
     * @param EntityInterface $originalEntity
     *
     * @return EntityInterface
     */
    protected function postProcessDeserializedEntity(EntityInterface $entity, EntityInterface $originalEntity = null)
    {
        return $entity;
    }

    protected function checkListActionAuthorization($user)
    {
    }

    protected function checkDetailActionAuthorization($user, EntityInterface $entity)
    {
    }

    protected function checkCreateActionAuthorization($user)
    {
    }

    protected function checkUpdateActionAuthorization($user, EntityInterface $entity)
    {
    }

    protected function checkDeleteActionAuthorization($user, EntityInterface $entity)
    {
    }

    /**
     * @return string
     */
    protected abstract function getEntityClass();
}
