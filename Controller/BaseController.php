<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BaseController extends FOSRestController
{

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return mixed
     *
     * @deprecated
     */
    protected function serializeRequestContent(Request $request, $type)
    {
        return $this->deserializeRequestContent($request, $type);
    }

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
        $object = $serializer->deserialize($content, $type, $request->getContentType());

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
}
