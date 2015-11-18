<?php


namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Entity\EntityInterface;
use Dontdrinkandroot\Repository\OrmEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractEntityController extends BaseController
{

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

    public function createAction(Request $request)
    {
        $entity = $this->deserializeRequestContent($request, $this->getEntityClass());
        $errors = $this->validate($entity);
        if (count($errors) > 0) {
            $view = $this->view($errors, 400);

            return $this->handleView($view);
        }

        $entity = $this->getRepository()->persist($entity);

        $view = $this->view($entity, 201);

        $view->setHeader(
            'Location',
            $this->generateUrl($this->getDetailRoute(), ['id' => $entity->getId()], true)
        );

        return $this->handleView($view);
    }

    public function updateAction(Request $request, $id)
    {
        $entity = $this->deserializeRequestContent($request, $this->getEntityClass());
        $errors = $this->validate($entity);
        if (count($errors) > 0) {
            $view = $this->view($errors, 400);

            return $this->handleView($view);
        }
        $entity = $this->getRepository()->merge($entity);

        $view = $this->view($entity, 200);

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
     * @return string
     */
    protected abstract function getEntityClass();

    /**
     * @return string
     */
    protected abstract function getDetailRoute();
}
