<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\Pagination\Pagination;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class DdrRestController extends FOSRestController
{

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return mixed
     */
    protected function deserializeRequestContent(Request $request, $type)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');
        $content = $request->getContent();
        $contentType = $request->getContentType();
        $object = $serializer->deserialize($content, $type, $contentType);

        return $object;
    }

    /**
     * @param mixed $object
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate($object)
    {
        $validator = $this->get('validator');
        $errors = $validator->validate($object);

        return $errors;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param View        $view
     * @param string      $route         The name of the route
     * @param mixed       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return View
     */
    protected function setLocationHeader(View $view, $route, $parameters, $referenceType)
    {
        $view->setHeader(
            'Location',
            $this->generateUrl($view, $parameters, $referenceType)
        );

        return $view;
    }

    /**
     * @param Pagination $pagination
     * @param View       $view
     */
    protected function addPaginationHeaders(Pagination $pagination, View $view)
    {
        $view->setHeader('X-Pagination-Current-Page', $pagination->getCurrentPage());
        $view->setHeader('X-Pagination-Per-Page', $pagination->getPerPage());
        $view->setHeader('X-Pagination-Total-Pages', $pagination->getTotalPages());
        $view->setHeader('X-Pagination-Total', $pagination->getTotal());
        $view->setHeader('X-Pagination', $pagination);
    }

    protected function createAndHandleForm(Request $request, $type, $data = null, array $options = [])
    {
        $form = null;
        if ('json' === $request->getRequestFormat()) {
            /* We want a form with no name in the JSON REST API, as the content is not prefixed with the form name */
            $form = $this->container->get('form.factory')->createNamed('', $type, $data, $options);
            $form->handleRequest($request);
            /* Forms without a name are not submitted automatically if the data was empty.
               We want to enforce validation. */
            if (!$form->isSubmitted()) {
                $form->submit([]);
            }
        } else {
            $form = parent::createForm($type, $data, $options);
            $form->handleRequest($request);
        }

        return $form;
    }
}
